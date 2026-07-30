<?php

    class Database
    {
        private static $connection = null;

        public static function connect()
        {
            if (self::$connection === null) {

                $config = require __DIR__ . '/../../config/database.php';

                try {
                    self::$connection = new PDO(
                        "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}",
                        $config['username'],
                        $config['password']
                    );

                    self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                } catch (PDOException $e) {
                    die("Erreur connexion base de données.");
                }
            }

            return self::$connection;
        }
    }