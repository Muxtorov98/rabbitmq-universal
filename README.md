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
composer require muxtorov98/rabbitmq-universal:^3.0 --ignore-platform-reqs --no-scripts
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

        // Test uchun javobni boshqa queue'ga yuborish
        $publisher = new RabbitPublisher();
        $publisher->publish('log_queue', [
            'status' => 'processed',
            'to' => $message['to'] ?? 'unknown',
        ]);
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