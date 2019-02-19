<?php
/**
 * WorkerJob
 * @package lib-worker
 * @version 0.0.1
 */

namespace LibWorker\Model;

class WorkerJob extends \Mim\Model
{

    protected static $table = 'worker_job';

    protected static $chains = [];

    protected static $q = [];
}