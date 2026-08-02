<?php

    class Database
    {
        private static $connection = null;

        /**
         * Retourne toujours la même connexion PDO (singleton).
         * Évite d'ouvrir une nouvelle connexion à chaque appel de Database::connect().
         */
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
                    self::$connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                    self::$connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

                } catch (PDOException $e) {
                    error_log("Erreur connexion base de données : " . $e->getMessage());
                    die("Erreur connexion base de données.");
                }
            }

            return self::$connection;
        }
    }
