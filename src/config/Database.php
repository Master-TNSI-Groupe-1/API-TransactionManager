<?php

class Database
{
    private static $instance = null;

    private $db;

    private function __construct($host, $port, $user, $password, $dbname)
    {
        $dbconfig = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8";
        $this->db = new PDO($dbconfig, $user, $password);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public static function getInstance(){
        if(is_null(self::$instance)){
            self::$instance = new Database("instance-transaction.czzxwrl2xwzb.us-east-1.rds.amazonaws.com", 3306, "root", "xpWzNptv8vb4KJp", "ApiRest");
            //self::$instance = new Database("localhost", 3307, "root", "", "apidb");
        }

        return self::$instance;
    }

    public function getDb(){
        return $this->db;
    }
}