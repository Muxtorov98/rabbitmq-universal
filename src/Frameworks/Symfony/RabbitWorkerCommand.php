<?php

namespace RabbitMQQueue\Frameworks\Symfony;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use RabbitMQQueue\Core\{EnvLoader, RabbitMQConnection, HandlerRegistry, Worker};

class RabbitWorkerCommand extends Command
{
    protected static $defaultName = 'rabbit:worker:start';

    protected function configure(): void
    {
        $this
            ->setName(self::$defaultName)
            ->setDescription('Start RabbitMQ worker listener');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>🚀 Loading environment...</info>');

        // .env yuklash
        $basePath = dirname(__DIR__, 3);
        EnvLoader::load($basePath);

        // RabbitMQ ulanish
        $connection = new RabbitMQConnection();
        $connection->connect();

        // Handlerlarni yuklash
        $path = $_ENV['HANDLER_PATH'] ?? $basePath . '/src/Handlers';
        $registry = new HandlerRegistry($path);

        $output->writeln('<info>👷 Worker started and listening for messages...</info>');

        // Worker’ni ishga tushirish
        (new Worker($connection, $registry))->start();

        return Command::SUCCESS;
    }
}