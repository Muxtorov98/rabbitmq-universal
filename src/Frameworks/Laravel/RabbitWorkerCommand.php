<?php

namespace RabbitMQQueue\Frameworks\Laravel;

use Illuminate\Console\Command;
use RabbitMQQueue\Core\{EnvLoader, RabbitMQConnection, HandlerRegistry, Worker};

class RabbitWorkerCommand extends Command
{
    protected $signature = 'rabbit:worker';
    protected $description = 'Start RabbitMQ worker listener';

    public function handle()
    {
        $this->info('🚀 Loading environment...');
        EnvLoader::load(base_path());

        $connection = new RabbitMQConnection();
        $connection->connect();

        $path = $_ENV['HANDLER_PATH'] ?? base_path('app/Handlers');
        $registry = new HandlerRegistry($path);

        $this->info('👷 Worker started...');
        (new Worker($connection, $registry))->start();
    }
}