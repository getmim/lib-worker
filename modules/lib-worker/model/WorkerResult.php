<?php
/**
 * WorkerResult
 * @package lib-worker
 * @version 0.0.1
 */

namespace LibWorker\Model;

class WorkerResult extends \Mim\Model
{

    protected static $table = 'worker_result';

    protected static $chains = [];

    protected static $q = [];
}