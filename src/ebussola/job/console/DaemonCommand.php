<?php
/**
 * Created by PhpStorm.
 * User: Leonardo
 * Date: 23/11/13
 * Time: 16:57
 */

namespace ebussola\job\console;


use Doctrine\DBAL\DriverManager;
use ebussola\job\Daemon;
use ebussola\job\jobdata\Doctrine;
use ebussola\job\Schedule;
use Monolog\Logger;
use Symfony\Bridge\Monolog\Handler\ConsoleHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DaemonCommand extends Command {

    protected function configure() {
        $this
            ->setName('jobschedule:start')
            ->setDescription('Starts the daemon process')
            ->addOption('db-config', 'c', InputOption::VALUE_REQUIRED, 'db-config.php file');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $config = include $input->getOption('db-config');
        $conn = DriverManager::getConnection($config['database']);
        $data = new Doctrine($conn, $config['table_name']);

        $handler = new ConsoleHandler($output);
        $logger = new Logger('job-schedule');
        $logger->pushHandler($handler);

        $schedule = new Schedule($data, $logger);
        $daemon = new Daemon($schedule, $logger);

        $logger->info('Daemon Started');
        $daemon->start();
    }

}
