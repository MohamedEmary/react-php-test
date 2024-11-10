<?php

namespace App\Database;

use Dotenv\Dotenv;
use Exception;
use PDO;

class Connection
{
  private static ?PDO $instance = null;

  public static function getInstance(): PDO
  {
    if (self::$instance === null) {
      try {
        $dotenv = Dotenv::createImmutable(dirname(__DIR__, 2));
        $dotenv->load();
        $dotenv->required(['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS'])->notEmpty();

        self::$instance = new PDO(
          sprintf(
            'mysql:host=%s;dbname=%s;charset=utf8mb4',
            $_ENV['DB_HOST'],
            $_ENV['DB_NAME']
          ),
          $_ENV['DB_USER'],
          $_ENV['DB_PASS'],
          [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
          ]
        );
      } catch (Exception $e) {
        echo ('Database connection failed: ' . $e->getMessage());
      }
    }
    return self::$instance;
  }
}
