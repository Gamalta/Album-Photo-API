<?php

class Database {

    static private $db;

    static public function initDatabase(){

        if(self::$db){
            return self::$db;
        }

        define('DB_DRIVER', 'mysql');
        define('DB_HOST', 'save.redblock.fr');
        define('DB_PORT', 3306);
        define('DB_NAME', 'Site');
        define('DB_USER', 'root');
        define('DB_PASS', 'Kodak2019');
        $dsn = DB_DRIVER.":dbname=".DB_NAME.";host=".DB_HOST.";port=".DB_PORT.";";
        
        try {

            self::$db = new PDO($dsn, DB_USER, DB_PASS);
            self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        }catch(Exception $e){
            throw new Exception("Database connection failled !");
             //Database connection fail
        }

        return self::$db;
    }

    static public function getDatabase(){
        return self::$db;
    }
}
?>