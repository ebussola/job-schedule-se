<?php
/**
 * Created by PhpStorm.
 * User: Leonardo
 * Date: 23/11/13
 * Time: 13:03
 */

class DoctrineTest extends PHPUnit_Framework_TestCase {

    /**
     * @var \ebussola\job\jobdata\Doctrine
     */
    private $data;

    private $table_name = 'job_test';

    public function setUp() {
        $conn = \Doctrine\DBAL\DriverManager::getConnection(array(
            'driver'   => 'pdo_sqlite',
            'user'     => 'root',
            'password' => '',
            'memory' => true
        ));
        $this->data = new \ebussola\job\jobdata\Doctrine($conn, $this->table_name);

        $helperSet = new \Symfony\Component\Console\Helper\HelperSet(array(
            'db' => new \Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper($conn)
        ));

        $cli = new \Symfony\Component\Console\Application();
        $cli->setHelperSet($helperSet);
        $cli->addCommands(array(
            new \ebussola\job\console\InitCommand()
        ));

        $cmd_tester = new \Symfony\Component\Console\Tester\CommandTester($cli->find('jobschedule:init'));
        $cmd_tester->execute(array(
            'command' => 'jobschedule:init',
            'table_name' => $this->table_name
        ));

        //Insert some data
        $conn->insert($this->table_name, array(
            'id' => 1,
            'expires_on' => strtotime('+1 hour'),
            'runner_class' => 'ebussola\\job\\runner',
            'schedule' => '@daily',
            'command' => 'foo'
        ));
        $conn->insert($this->table_name, array(
            'id' => 2,
            'expires_on' => strtotime('+1 hour'),
            'runner_class' => 'ebussola\\job\\runner',
            'schedule' => '@daily',
            'command' => 'bar'
        ));
    }

    public function testFindAll() {
        $jobs = $this->data->getAll();
        foreach ($jobs as $job) {
            $this->assertInstanceOf('\ebussola\job\Job', $job);
            $this->assertNotNull($job->command);
        }
    }

    public function testFind() {
        $job = $this->data->find(1);
        $this->assertInstanceOf('\ebussola\job\Job', $job);
        $this->assertNotNull($job->command);
    }

}