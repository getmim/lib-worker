<?php
/**
 * Worker
 * @package lib-worker
 * @version 0.3.0
 */

namespace LibWorker\Library;

use LibWorker\Model\WorkerJob as WJob;

class Worker
{

    static public function addMany(array $data): bool{
        $rows = [];
        foreach($data as $datum){
            $rows[] = [
                'name'   => $datum['name'],
                'router' => json_encode($datum['router']),
                'data'   => json_encode($datum['data']),
                'time'   => date('Y-m-d H:i:s', $datum['time'])
            ];
        }

        if(!$rows)
            return false;

        return WJob::createMany($rows, true);
    }

    static public function add(string $name, array $router, array $data, int $time): bool {
        if(self::exists($name))
            return false;

        return !!WJob::create([
            'name'   => $name,
            'router' => json_encode($router),
            'data'   => json_encode($data),
            'time'   => date('Y-m-d H:i:s', $time)
        ]);
    }

    static public function get(string $name): ?object {
        $job = WJob::getOne(['name'=>$name]);
        if(!$job)
            return null;

        $job->router = json_decode($job->router);
        $job->data   = json_decode($job->data);

        return $job;
    }

    static public function exists(string $name): bool {
        return !!WJob::get(['name'=>$name], false);
    }

    static public function remove(string $name): bool {
        return !!WJob::remove(['name'=>$name]);
    }
}
