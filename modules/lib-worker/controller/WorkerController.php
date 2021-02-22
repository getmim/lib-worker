<?php
/**
 * WorkerController
 * @package lib-model
 * @version 0.3.0
 */

namespace LibWorker\Controller;

use LibWorker\Model\WorkerJob as WJob;
use LibWorker\Model\WorkerResult as WResult;

use LibCurl\Library\Curl;

use Cli\Library\Bash;

use Mim\Library\Fs;
use Mim\Library\Router;

class WorkerController extends \Cli\Controller
{
    private $pid_file;
    private $stopper_file;
    private $workers = [];

    private function getPIDFile(): string{
        if($this->pid_file)
            return $this->pid_file;

        $pid_file = $this->config->libWorker->pidFile;
        if(substr($pid_file,0,1) != '/')
            $pid_file = realpath( BASEPATH . '/' . $pid_file );

        $pid_file.= '/.worker';

        return ( $this->pid_file = $pid_file );
    }

    private function getPID(): ?string{
        if(!$this->isWorking())
            return null;
        $pid_file = $this->getPIDFile();
        return file_get_contents($pid_file);
    }

    private function getStopperFile(): string{
        if($this->stopper_file)
            return $this->stopper_file;

        return $this->stopper_file = dirname($this->getPIDFile()) . '/.wstopper';
    }

    private function isWorking(): bool{
        $pid_file = $this->getPIDFile();
        if(!is_file($pid_file))
            return false;

        $pid = file_get_contents($pid_file);
        if(!posix_getpgid($pid))
            return false;

        $pwd = trim(preg_replace('!^[0-9]+: !', '', `lsof -a -p $pid -d cwd -n | tail -1 | awk '{print \$NF}'`));
        return $pwd === BASEPATH;
    }

    private function saveResult(string $name, string $source, string $result) {
        if(!\Mim::$app->config->libWorker->keepResponse)
            return;

        WResult::create([
            'name'   => $name,
            'source' => $source,
            'result' => $result
        ]);
    }

    private function scanChildProcess(): void{
        foreach($this->workers as $job => $res){
            $stat = proc_get_status($res);
            if($stat['running'])
                continue;
            unset($this->workers[$job]);
            Bash::echo(' - Job ' . $job . ' Done');
            break;
        }
    }

    public function onShutdown(){
        $pid_file = $this->getPIDFile();
        if(is_file($pid_file))
            unlink($pid_file);
        $stopper_file = $this->getStopperFile();
        if(is_file($stopper_file))
            unlink($stopper_file);
        exit;
    }

    public function pidAction(){
        if(!($pid = $this->getPID()))
            return Bash::echo('Stopped');
        Bash::echo('PID: ' . $pid);
    }

    public function runAction(){
        $id = $this->req->param->id;
        $job = WJob::getOne(['id'=>$id]);
        if(!$job)
            return;

        $time = strtotime($job->time);
        if($time > time())
            return;

        $router = json_decode($job->router, true);

        if(!$this->router->exists($router[0])){
            WJob::remove(['id'=>$id]);
            return self::saveResult(
                $job->name,
                json_encode($job),
                json_encode('__router_not_found__')
            );
        }

        $target = call_user_func_array([$this->router, 'to'], $router);

        $route_gate = Router::$all_routes->_gateof->{$router[0]};
        $gate = \Mim::$app->config->gates->$route_gate;

        $type = $gate->host->value === 'CLI' ? 'cli' : 'curl';

        $result = null;

        if($type === 'curl'){
            $retry = WResult::count(['name' => $job->name]);
            $result = Curl::fetch([
                'url'     => $target,
                'method'  => 'POST',
                'body'    => $job->data,
                'agent'   => 'Mim Worker',
                'headers' => [
                    'Accept'        => 'application/json',
                    'Content-Type'  => 'application/json',
                    'X-Retry'       => $retry
                ]
            ]);
        }else{
            $php_binary = $this->config->libWorker->phpBinary;
            $target = 'cd ' . BASEPATH . ' && ' . $php_binary . ' index.php ' . $target;
            $result = `$target`;
            $lines  = explode(PHP_EOL, trim($result));
            $line   = end($lines);
            $result = $line;
        }

        if(!is_object($result))
            $result = json_decode($result);

        if(!$result){
            WJob::set(['time'=>date('Y-m-d H:i:s', strtotime('+1 minutes'))], ['id'=>$job->id]);
            return self::saveResult($job->name, json_encode($job), json_encode(''));
        }

        if($result->error){
            $delay = 60 * 2;
            if(isset($result->delay))
                $delay = $result->delay;
            $next = time() + $delay;
            WJob::set(['time'=>date('Y-m-d H:i:s', $next)], ['id'=>$job->id]);
            return self::saveResult($job->name, json_encode($job), json_encode($result));
        }

        WJob::remove(['id'=>$job->id]);
        self::saveResult($job->name, json_encode($job), json_encode($result));
    }

    public function startAction(){
        if($this->isWorking())
            return Bash::echo('Started');

        // clean stuff on exit
        pcntl_signal(SIGTERM, [$this, 'onShutdown']);
        pcntl_signal(SIGINT,  [$this, 'onShutdown']);
        register_shutdown_function([$this, 'onShutdown']);

        Fs::write($this->getPIDFile(), getmypid());

        $php_binary = $this->config->libWorker->phpBinary;
        $max_worker = (int)$this->config->libWorker->concurency;

        while(true){
            if(is_file($this->getStopperFile()))
                break;

            $jobs = WJob::get(['time' => ['__op', '<', date('Y-m-d H:i:s')]], 25);

            // sleep more
            if($jobs){
                Bash::echo(date('Y-m-d H:i:s') . ' | Running The Jobs');
                foreach($jobs as $job){
                    if(isset($this->workers[$job->id]))
                        continue;
                    if(is_file($this->getStopperFile()))
                        break 2;

                    while($max_worker <= count($this->workers)){
                        Bash::echo(date('Y-m-d H:i:s') . ' | Wait For Child Process Slot');
                        if(is_file($this->getStopperFile()))
                            break 3;
                        $this->scanChildProcess();
                        sleep(1);
                    }

                    $cmd = $php_binary . ' index.php worker run ' . $job->id;
                    $worker_desc = [
                        ['pipe', 'r'],
                        ['pipe', 'w'],
                        ['file', '/tmp/mim-worker-error-' . $job->id . '.txt', 'a']
                    ];

                    $this->workers[$job->id] = proc_open($cmd, $worker_desc, $pipes, BASEPATH);
                    Bash::echo(date('Y-m-d H:i:s') . ' | ' .$cmd);
                    Bash::echo(date('Y-m-d H:i:s') . ' | Running Job ' . $job->id);
                }
            }

            $this->scanChildProcess();
            sleep(2);

            Bash::echo(date('Y-m-d H:i:s') . ' | Next Loop');
        }

        Bash::echo(date('Y-m-d H:i:s') . ' | Stopping...');
        while(count($this->workers)){
            $this->scanChildProcess();
            sleep(1);
        }
    }

    public function statusAction(){
        Bash::echo($this->isWorking() ? 'Started' : 'Stopped');
    }

    public function stopAction(){
        if(!$this->isWorking())
            return Bash::echo('Stopped');

        $stopper_file = $this->getStopperFile();
        if(is_file($stopper_file))
            return Bash::echo('Other stopper is running');

        touch($stopper_file);

        $pid = $this->getPID();
        while(posix_getpgid($pid))
            sleep(1);

        Bash::echo('Stopped');
    }
}
