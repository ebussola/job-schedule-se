<?php
/**
 * Created by PhpStorm.
 * User: Leonardo
 * Date: 23/11/13
 * Time: 09:01
 */

namespace ebussola\job\jobdata;


use Doctrine\DBAL\Connection;
use ebussola\job\Job;
use ebussola\job\JobData;

class Doctrine implements JobData {

    /**
     * @var Connection
     */
    private $conn;

    /**
     * @var string
     */
    private $table_name;

    public function __construct(Connection $conn, $table_name) {
        $this->conn = $conn;
        $this->table_name = $table_name;
    }

    /**
     * @param int $command_id
     *
     * @return Job
     */
    public function find($command_id) {
        $this->conn->connect();

        $stmt = $this->conn->query("select * from {$this->table_name} where id = :id");
        $stmt->bindValue(':id', $command_id, \PDO::PARAM_INT);
        $stmt->setFetchMode(\PDO::FETCH_CLASS, '\ebussola\job\job\Job');
        $stmt->execute();

        $job = $stmt->fetch();

        $this->conn->close();

        return $job;
    }

    /**
     * @return Job[]
     */
    public function getAll() {
        $this->conn->connect();

        $stmt = $this->conn->query("select * from {$this->table_name}");
        $stmt->setFetchMode(\PDO::FETCH_CLASS, '\ebussola\job\job\Job');
        $stmt->execute();

        $jobs = $stmt->fetchAll();

        $this->conn->close();

        return $jobs;
    }

}