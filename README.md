# 🐇 RabbitMQ Universal Queue Worker (Laravel, Symfony, Yii2)

**Universal RabbitMQ Queue System** — bu PHP 8+ uchun ishlab chiqilgan **framework-agnostic** kutubxona bo‘lib,  
Laravel, Symfony va Yii2 loyihalarida **xabar yuborish (publish)** va **qabul qilish (consume)** jarayonlarini **bir xil sintaksisda** amalga oshirish imkonini beradi.

---

## 🚀 Asosiy xususiyatlar

✅ Laravel, Symfony va Yii2 bilan avtomatik moslashadi  
✅ `.env` orqali sozlanadi — qo‘shimcha config talab etilmaydi  
✅ Auto reconnect & retry mexanizmi  
✅ QoS, prefetch, confirm mode (ACK) qo‘llab-quvvatlanadi  
✅ PSR-4 autoload va PSR-3 logging  
✅ Worker backgroundda doimiy ishlaydi (Supervisor yoki Docker bilan)

---

## 📦 O‘rnatish

```bash
composer require muxtorov98/rabbitmq-universal:v2.1.8 --ignore-platform-reqs

docker compose exec php composer require muxtorov98/rabbitmq-universal:v2.1.8 --ignore-platform-reqs
```

---

## ⚙️ `.env` konfiguratsiyasi

Loyha ildizida `.env` fayl yarating:

```dotenv
RABBITMQ_HOST='localhost'
RABBITMQ_PORT=5672
RABBITMQ_USER='muxtorov'
RABBITMQ_PASS='5upris#1eWata2ped'
RABBITMQ_VHOST='/'
RABBITMQ_PREFETCH=10
RABBITMQ_SSL=false

# Worker handler fayllar joylashgan joy
HANDLER_PATH='app/Handlers'
```

---

## 🧩 Umumiy Worker va Publisher misoli

### 🔧 Handler (har uchala framework uchun bir xil)
`app/Handlers/EmailHandler.php`:

```php
<?php

namespace App\Handlers;

use RabbitMQQueue\Core\QueueHandlerInterface;
use RabbitMQQueue\Core\QueueChannel;
use RabbitMQQueue\Core\RabbitPublisher;

#[QueueChannel('email_queue')]
class EmailHandler implements QueueHandlerInterface
{

    public function handle(array $message): void
    {
        echo "📩 Email received: " . json_encode($message, JSON_UNESCAPED_UNICODE) . PHP_EOL;
    }
}
```

---

### ✉️ Publish qilish (hamma frameworklarda bir xil)

```php
use RabbitMQQueue\Core\RabbitPublisher;

$publisher = new RabbitPublisher();
$publisher->publish('email_queue', [
    'to' => 'user@example.com',
    'subject' => 'Universal publish test!'
]);
```

---

## ⚙️ Laravel integratsiyasi

### 🪄 1. Avtomatik yuklash
Paket avtomatik tarzda `RabbitMQServiceProvider` ni yuklaydi, qo‘shimcha ro‘yxatdan o‘tkazish talab etilmaydi.

### 🏃 2. Worker ishga tushirish
```bash
php artisan rabbit:worker

docker compose exec php php artisan rabbit:worker
```

---

## ⚙️ Symfony integratsiyasi

### ⚙️ 1. `services.yaml` konfiguratsiyasi

`config/services.yaml` fayliga quyidagilarni qo‘shing:

```yaml
RabbitMQQueue\Frameworks\Symfony\:
    resource: '../vendor/muxtorov98/rabbitmq-universal/src/Frameworks/Symfony/*'
    tags: [ 'console.command' ]
```

### 🏃 2. Worker ishga tushirish

```bash
php bin/console rabbit:worker:start
```

---

## ⚙️ Yii2 integratsiyasi

### ⚙️ 1. `console/config/main.php` faylida controllerMap sozlovi

```php
'controllerMap' => [
    'worker' => [
        'class' => \RabbitMQQueue\Frameworks\Yii2\WorkerController::class,
    ],
],
```

### 🏃 2. Worker ishga tushirish

```bash
php yii worker/start
```
# laravel

- app/Console/Commands/RabbitPublishCommand.php

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use RabbitMQQueue\Core\RabbitPublisher;

class RabbitPublishCommand extends Command
{
    protected $signature = 'rabbit:publish {queue} {--data=}';
    protected $description = 'Publish a message to RabbitMQ queue (from Laravel)';

    public function handle()
    {
        $queue = $this->argument('queue');
        $data = $this->option('data')
            ? json_decode($this->option('data'), true)
            : ['text'=>'Salom from Laravel'];

        $publisher = new RabbitPublisher();
        $publisher->publish($queue, $data);

        $this->info("📩 Message published to queue '{$queue}' from Laravel: " . json_encode($data, JSON_UNESCAPED_UNICODE));
    }
}
```
```bash
  php artisan rabbit:publish log_queue --data='{"status":"processed","to":"user@example.com","text":"Salom from Laravel"}'
```

# symfony

- src/Command/RabbitPublishCommand.php

```php
<?php

namespace App\Command;

use RabbitMQQueue\Core\RabbitPublisher;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RabbitPublishCommand extends Command
{
    protected static $defaultName = 'rabbit:publish';

    protected function configure()
    {
        $this
            ->setDescription('Publish a message to RabbitMQ queue (from Symfony)')
            ->addArgument('queue', InputArgument::REQUIRED, 'Queue name')
            ->addOption('data', null, InputOption::VALUE_OPTIONAL, 'JSON data string');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $queue = $input->getArgument('queue');
        $data = $input->getOption('data')
            ? json_decode($input->getOption('data'), true)
            : ['text'=>'Salom from Symfony'];

        $publisher = new RabbitPublisher();
        $publisher->publish($queue, $data);

        $output->writeln("📩 Message published to queue '{$queue}' from Symfony: " . json_encode($data, JSON_UNESCAPED_UNICODE));
        return Command::SUCCESS;
    }
}

```
```bash
php bin/console rabbit:publish notification_queue --data='{"event":"user_registered","user_id":12345,"text":"Salom from Symfony"}'
```

# yii 

- console/controllers/RabbitPublishController.php

```php
<?php
namespace console\controllers;

use yii\console\Controller;
use RabbitMQQueue\Core\RabbitPublisher;
use yii\helpers\Json;

class RabbitPublishController extends Controller
{
    public $data;

    public function options($actionID)
    {
        return ['data'];
    }

    public function actionIndex($queue)
    {
        $data = $this->data ? Json::decode($this->data) : [];

        $publisher = new RabbitPublisher();
        $publisher->publish($queue, $data);

        $this->stdout("Message published to queue '{$queue}' from Yii2\n");
    }
}
```
```bash
php yii rabbit-publish email_queue --data='{"to":"user@example.com","subject":"Yii2 to Laravel"}'
```
