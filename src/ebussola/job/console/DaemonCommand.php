<?php
/**
 * Created by PhpStorm.
 * User: Leonardo
 * Date: 23/11/13
 * Time: 16:57
 */

namespace ebussola\job\console;

use ebussola\job\Daemon;
use ebussola\job\jobdata\Doctrine;
use ebussola\job\Schedule;
use Monolog\Logger;
use Symfony\Bridge\Monolog\Handler\ConsoleHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DaemonCommand extends Command {

    protected function configure() {
        $this
            ->setName('jobschedule:start')
            ->setDescription('Starts the daemon process')
            ->addArgument('table_name', InputArgument::REQUIRED, 'Table Name');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $table_name = $input->getArgument('table_name');
        $conn = $this->getHelper('db')->getConnection();
        $data = new Doctrine($conn, $table_name);

        $handler = new ConsoleHandler($output);
        $logger = new Logger('job-schedule');
        $logger->pushHandler($handler);

        $schedule = new Schedule($data, $logger);
        $daemon = new Daemon($schedule, $logger);

        $logger->info('Daemon Started');
        $daemon->start();
    }

}