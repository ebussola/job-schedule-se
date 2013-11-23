<?php
/**
 * Created by PhpStorm.
 * User: Leonardo
 * Date: 23/11/13
 * Time: 13:09
 */

namespace ebussola\job\console;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InitCommand extends Command {

    protected function configure() {
        $this
            ->setName('jobschedule:init')
            ->setDescription('Setup the database')
            ->addArgument('table_name', InputArgument::REQUIRED, 'Name of the table to be configured on database');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        /** @var Connection $conn */
        $conn = $this->getHelper('db')->getConnection();
        $table_name = $input->getArgument('table_name');

        $schema = new Schema();
        $table = $schema->createTable($table_name);
        $table->addColumn('id', 'integer');
        $table->addColumn('expires_on', 'integer');
        $table->addColumn('parent_id', 'integer', array('notnull' => false));
        $table->addColumn('runner_class', 'string');
        $table->addColumn('schedule', 'string');
        $table->addColumn('command', 'string');
        $table->setPrimaryKey(array('id'));
        $table->addForeignKeyConstraint($table, array('parent_id'), array('id'), array('onDelete' => 'CASCADE'));

        $sqls = $schema->toSql($conn->getDatabasePlatform());

        foreach ($sqls as $sql) {
            $output->writeln('Executing: '.$sql);
            $conn->exec($sql);
        }

        $output->writeln('Done!');
    }

}