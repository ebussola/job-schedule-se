<?php
/**
 * Created by PhpStorm.
 * User: Leonardo
 * Date: 23/11/13
 * Time: 16:07
 */

class SymfonyProcessTest extends PHPUnit_Framework_TestCase {

    /**
     * @var \ebussola\job\jobrunner\SymfonyProcess
     */
    private $symfony_process;

    public function setUp() {
        $this->symfony_process = new \ebussola\job\jobrunner\SymfonyProcess();
    }

    public function testRunIt() {
        $job = new \ebussola\job\job\Job();
        $job->id = 1;
        $job->status_code = 1;
        $job->command = 'sleep 5';

        $this->symfony_process->runIt($job, function() {
            echo 'I\'m done!';
        });

        $this->assertTrue($this->symfony_process->isRunning($job));
    }

    public function testRunItWithDependencies() {
        $job = new \ebussola\job\job\Job();
        $job->id = 1;
        $job->status_code = 1;
        $job->command = 'sleep 5';

        $job2 = new \ebussola\job\job\Job();
        $job2->id = 2;
        $job2->status_code = 4;
        $job2->parent_id = 1;
        $job2->command = 'sleep 1';

        $this->symfony_process->runIt($job, function() {
            echo 'I\'m done!';
        });
        $this->symfony_process->runIt($job2, function() {
            echo 'I\'m done!';
        });

        while ($this->symfony_process->isRunning($job));

        $this->assertTrue($this->symfony_process->isRunning($job2));
    }

}
 