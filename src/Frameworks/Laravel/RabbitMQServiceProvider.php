<?php

namespace RabbitMQQueue\Frameworks\Laravel;

use Illuminate\Support\ServiceProvider;
use RabbitMQQueue\Core\RabbitMQConnection;
use RabbitMQQueue\Core\EnvLoader;

class RabbitMQServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(RabbitMQConnection::class, function ($app) {
            EnvLoader::load(base_path());
            $connection = new RabbitMQConnection();
            $connection->connect();
            return $connection;
        });
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                RabbitWorkerCommand::class,
            ]);
        }
    }
}