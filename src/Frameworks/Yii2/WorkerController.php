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

        // 🔧 Root path (.env joylashgan katalog)
        $rootPath = dirname(Yii::getAlias('@app'), 2);
        if (!file_exists($rootPath . '/.env')) {
            $rootPath = Yii::getAlias('@app');
        }

        EnvLoader::load($rootPath);

        // 🔌 RabbitMQ ulanish
        $connection = new RabbitMQConnection();
        $connection->connect();

        // 🔍 Handler path aniqlash
        $handlerPath = $_ENV['HANDLER_PATH'] ?? '@console/Handlers';

        // 1️⃣ Yii aliasni sinaymiz
        $resolvedPath = Yii::getAlias($handlerPath, false);

        // 2️⃣ Agar alias topilmasa, relative pathni absolute qilib olamiz
        if (!$resolvedPath) {
            $resolvedPath = $rootPath . DIRECTORY_SEPARATOR . ltrim($handlerPath, '/');
        }

        // 3️⃣ Agar papka mavjud bo‘lmasa, avtomatik yaratamiz
        if (!is_dir($resolvedPath)) {
            @mkdir($resolvedPath, 0777, true);
            $this->stdout("⚠️  Handler papkasi topilmadi, yaratildi: {$resolvedPath}\n");
        }

        // 🔁 Registry va Worker
        $registry = new HandlerRegistry($resolvedPath);

        $this->stdout("👷 Worker started and listening for messages...\n");
        (new Worker($connection, $registry))->start();
    }
}