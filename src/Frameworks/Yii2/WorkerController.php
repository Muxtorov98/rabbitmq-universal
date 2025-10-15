<?php

namespace RabbitMQQueue\Frameworks\Yii2;

use yii\console\Controller;
use RabbitMQQueue\Core\{EnvLoader, RabbitMQConnection, HandlerRegistry, Worker};
use Yii;

class WorkerController extends Controller
{
    public function actionStart(): void
    {
        $this->stdout("🚀 Loading environment...\n");

        // 🔧 Root path avtomatik aniqlanadi (.env joylashgan katalog)
        // Yii2 Advanced uchun: /yii2-app-advanced/
        // Yii2 Basic uchun: /yii2-basic/
        $rootPath = dirname(Yii::getAlias('@app'), 2);
        if (!file_exists($rootPath . '/.env')) {
            // Agar .env fayl console papkada bo‘lsa
            $rootPath = Yii::getAlias('@app');
        }

        EnvLoader::load($rootPath);

        // 🔌 RabbitMQ ulanish
        $connection = new RabbitMQConnection();
        $connection->connect();

        // 🔍 Handler path aniqlash
        $handlerPath = $_ENV['HANDLER_PATH'] ?? '@app/handlers';
        $resolvedPath = Yii::getAlias($handlerPath, false) ?: $handlerPath;

        $registry = new HandlerRegistry($resolvedPath);

        // 👷 Worker ishga tushadi
        $this->stdout("👷 Worker started and listening for messages...\n");
        (new Worker($connection, $registry))->start();
    }
}