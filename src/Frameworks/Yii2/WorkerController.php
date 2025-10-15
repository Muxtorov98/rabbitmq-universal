<?php

namespace RabbitMQQueue\Frameworks\Yii2;

use RabbitMQQueue\Core\{EnvLoader, RabbitMQConnection, HandlerRegistry, Worker};
use Yii;

class WorkerController extends \yii\console\Controller
{
    public function actionStart(): void
    {
        $this->stdout("🚀 Loading environment...\n");
        EnvLoader::load(Yii::getAlias('@root'));

        $connection = new RabbitMQConnection();
        $connection->connect();

        $path = $_ENV['HANDLER_PATH'] ?? '@console/handlers';
        $registry = new HandlerRegistry(Yii::getAlias($path));

        $this->stdout("👷 Worker started...\n");
        (new Worker($connection, $registry))->start();
    }
}