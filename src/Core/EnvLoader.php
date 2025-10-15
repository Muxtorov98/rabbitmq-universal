<?php

namespace RabbitMQQueue\Core;

use Dotenv\Dotenv;

class EnvLoader
{
    public static function load(string $basePath): void
    {
        $envFile = rtrim($basePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '.env';

        if (!file_exists($envFile)) {
            echo "⚠️  .env file not found in: {$basePath}\n";
            return;
        }

        // PHP dotenv orqali .env yuklash
        $dotenv = Dotenv::createImmutable($basePath);
        $dotenv->safeLoad();

        // Har bir o‘zgaruvchini $_ENV ichiga joylaymiz
        foreach ($_ENV as $key => $value) {
            putenv("$key=$value");
            $_SERVER[$key] = $value;
        }

        echo "✅ .env loaded successfully from: {$basePath}\n";
    }
}