<?php

namespace Yii2;

use RabbitMQQueue\Core\{EnvLoader, RabbitMQConnection, HandlerRegistry, Worker};
use Yii;

class WorkerController extends \yii\console\Controller
{
    public function actionStart(): void
    {
        EnvLoader::load(Yii::getAlias('@root')); // .env yuklash
        $connection = new RabbitMQConnection();
        $connection->connect();

        // .env dan path olamiz
        $path = $_ENV['HANDLER_PATH'] ?? '@console/handlers';
        $registry = new HandlerRegistry(Yii::getAlias($path));

        $worker = new Worker($connection, $registry);
        $worker->start();
    }
}