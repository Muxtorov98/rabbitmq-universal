<?php

namespace Symfony;

use RabbitMQQueue\Core\{EnvLoader, RabbitMQConnection, HandlerRegistry, Worker};

class RabbitWorkerCommand extends Command
{
    protected static $defaultName = 'rabbit:worker:start';

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        EnvLoader::load(__DIR__ . '/../../../'); // .env yuklash
        $connection = new RabbitMQConnection();
        $connection->connect();

        $path = $_ENV['HANDLER_PATH'] ?? __DIR__ . '/../../Handlers';
        $registry = new HandlerRegistry($path);

        $worker = new Worker($connection, $registry);
        $worker->start();

        return Command::SUCCESS;
    }
}