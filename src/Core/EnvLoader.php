<?php

namespace RabbitMQQueue\Core;

use Dotenv\Dotenv;

class EnvLoader
{
    public static function load(string $baseDir): void
    {
        if (file_exists($baseDir . '/.env')) {
            $dotenv = Dotenv::createImmutable($baseDir);
            $dotenv->load();
        }
    }
}