<?php
/**
 * Created by PhpStorm.
 * User: Leonardo
 * Date: 23/11/13
 * Time: 15:43
 */

namespace ebussola\job\jobrunner;

use ebussola\job\Job;
use Symfony\Component\Process\Process;

class SymfonyProcess implements \ebussola\job\JobRunner {

    /**
     * @var \ebussola\job\jobrunner\symfonyprocess\Job[]
     */
    private $running;

    /**
     * @var \ebussola\job\jobrunner\symfonyprocess\Job[]
     */
    private $waiting;

    public function __construct() {
        $this->running = array();
        $this->waiting = array();
    }

    /**
     * @param \ebussola\job\Job $job
     * @param callable          $callback
     *
     * @return mixed
     */
    public function runIt(\ebussola\job\Job $job, $callback) {
        if (!$job instanceof \ebussola\job\jobrunner\symfonyprocess\Job) {
            $job = new \ebussola\job\jobrunner\symfonyprocess\Job($job);
        }

        $job->callback = $callback;

        switch ($job->status_code) {
            case 1 :
            case 3 :
                $job->process = $process = new Process($job->command);
                $process->start();
                $this->running[] = $job;
                break;

            case 4 :
                $this->waiting[] = $job;
                break;
        }
    }

    /**
     * @param \ebussola\job\Job $cmd
     *
     * @return bool
     */
    public function isRunning(\ebussola\job\Job $job) {
        $this->refreshJobs($job);

        return (isset($job->process) && $job->process->isRunning());
    }

    /**
     * @param \ebussola\job\Job $job
     *
     * @return bool
     */
    public function isWaiting(\ebussola\job\Job $job) {
        $this->refreshJobs($job);

        return in_array($job, $this->waiting);
    }

    /**
     * @param \ebussola\job\jobrunner\symfonyprocess\Job $job
     */
    private function refreshJobs(\ebussola\job\Job $job) {
        if (in_array($job, $this->running)) {
            $key = array_search($job, $this->running);
            /** @var Process $process */
            $process = $job->process;
            if ($process->isTerminated()) {
                unset($this->running[$key]);
                $job->status_code = 0;

                call_user_func($job->callback, $job);

                foreach ($this->waiting as $w_key => $job_waiting) {
                    if ($job_waiting->parent_id == $job->id) {
                        unset($this->waiting[$w_key]);
                        $job_waiting->status_code = 1;
                        $this->runIt($job_waiting, $job_waiting->callback);
                    }
                }
            }
        }
    }

}