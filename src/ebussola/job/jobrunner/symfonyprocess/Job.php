<?php
/**
 * Created by PhpStorm.
 * User: Leonardo
 * Date: 23/11/13
 * Time: 16:48
 */

namespace ebussola\job\jobrunner\symfonyprocess;


use Symfony\Component\Process\Process;

class Job implements \ebussola\job\Job {

    /**
     * @var \ebussola\job\Job
     */
    private $job;

    /**
     * @var Process
     */
    public $process;

    public $callback;

    public function __construct(\ebussola\job\Job $job) {
        $this->job = $job;
    }

    public function __get($name) {
        return $this->job->{$name};
    }

    public function __set($name, $value) {
        $this->job->{$name} = $value;
    }

}