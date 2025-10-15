<?php

use RabbitMQQueue\Core\{EnvLoader, RabbitMQConnection, HandlerRegistry, Worker};

class RabbitWorkerCommand extends Command
{
    protected $signature = 'rabbit:worker';
    protected $description = 'Start RabbitMQ worker';

    public function handle()
    {
        EnvLoader::load(base_path());
        $connection = new RabbitMQConnection();
        $connection->connect();

        $path = $_ENV['HANDLER_PATH'] ?? base_path('app/Handlers');
        $registry = new HandlerRegistry($path);

        $worker = new Worker($connection, $registry);
        $worker->start();
    }
}