<?php

namespace RabbitMQQueue\Frameworks\Symfony;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use RabbitMQQueue\Core\{EnvLoader, RabbitMQConnection, HandlerRegistry, Worker};

class RabbitWorkerCommand extends Command
{
    protected static $defaultName = 'rabbit:worker:start';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('🚀 Loading environment...');

        // .env yuklash
        $basePath = dirname(__DIR__, 3);
        EnvLoader::load($basePath);

        // RabbitMQ ulanadi
        $connection = new RabbitMQConnection();
        $connection->connect();

        // Handlerlar ro‘yxatini yuklash
        $path = $_ENV['HANDLER_PATH'] ?? $basePath . '/app/Handlers';
        $registry = new HandlerRegistry($path);

        $output->writeln("👷 Worker started and listening for messages...");

        // Worker’ni ishga tushirish
        (new Worker($connection, $registry))->start();

        // 🔥 Muhim: Symfony 5+ uchun `int` qaytarish majburiy
        return Command::SUCCESS;
    }
}