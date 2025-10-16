# 🐇 RabbitMQ Universal Queue Worker (Laravel, Symfony, Yii2)

**Universal RabbitMQ Queue System** — bu PHP 8+ uchun ishlab chiqilgan **framework-agnostic** kutubxona bo‘lib,  
Laravel, Symfony va Yii2 loyihalarida **xabar yuborish (publish)** va **qabul qilish (consume)** jarayonlarini bir xil sintaksisda amalga oshirish imkonini beradi.

---

## 🚀 Xususiyatlar

✅ Laravel, Symfony, Yii2 bilan avtomatik moslashadi  
✅ `.env` orqali sozlanadi — hech qanday qo‘shimcha config kerak emas  
✅ Auto reconnect & retry mexanizmi  
✅ QoS, prefetch, confirm mode (acknowledgment) qo‘llab-quvvatlanadi  
✅ PSR-4 autoload va PSR-3 logging mos  
✅ Worker backgroundda doimiy ishlaydi (Supervisor yoki Docker bilan)

---

## 📦 O‘rnatish

```bash
composer require muxtorov98/rabbitmq-universal:^3.0
```

---

## ⚙️ `.env` konfiguratsiyasi

Loyha ildizida `.env` fayl yarating:

```dotenv
RABBITMQ_HOST=localhost
RABBITMQ_PORT=5672
RABBITMQ_USER=muxtorov
RABBITMQ_PASS=5upris#1eWata2ped
RABBITMQ_VHOST=/
RABBITMQ_PREFETCH=10
RABBITMQ_SSL=false

# handler fayllar joylashgan manzil
HANDLER_PATH=app/Handlers
```

---

## 🧩 1. Laravelda ishlatish

### 🔧 1.1 Service Provider
Paket avtomatik tarzda `RabbitMQServiceProvider` ni yuklaydi (`composer.json` orqali).

### ✉️ 1.2 Worker yaratish
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
    public function __construct(private RabbitPublisher $rabbitPublisher) {}

    public function handle(array $message): void
    {
        echo "📩 Email received: " . json_encode($message, JSON_UNESCAPED_UNICODE) . "\n";

        // Test uchun javobni boshqa queue'ga yuboramiz
        $this->rabbitPublisher->publish('log_queue', [
            'status' => 'processed',
            'to' => $message['to'] ?? 'unknown'
        ]);
    }
}
```

### 🏃 1.3 Worker ishga tushirish
```bash
php artisan rabbit:worker
```

### 🚀 1.4 Publish qilish
```php
use RabbitMQQueue\Core\RabbitPublisher;

$publisher = new RabbitPublisher();
$publisher->publish('email_queue', [
    'to' => 'user@example.com',
    'subject' => 'Hello from Laravel!'
]);
```

---

## ⚙️ 2. Symfony’da ishlatish

### 📂 2.1 Worker Command
Paket `RabbitWorkerCommand` ni avtomatik ro‘yxatdan o‘tkazadi.

```bash
php bin/console rabbit:worker:start
```

### ✉️ 2.2 Publish qilish
```php
use RabbitMQQueue\Core\RabbitPublisher;

$publisher = new RabbitPublisher();
$publisher->publish('email_queue', [
    'to' => 'symfony@example.com',
    'subject' => 'From Symfony Worker!'
]);
```

---

## ⚙️ 3. Yii2’da ishlatish

### 📂 3.1 Worker Controller
```bash
php yii worker/start
```

### ✉️ 3.2 Publish qilish
```php
$publisher = new \RabbitMQQueue\Core\RabbitPublisher();
$publisher->publish('email_queue', [
    'to' => 'yii2@example.com',
    'subject' => 'Yii2 integration success!'
]);
```

---

## 🔁 Worker konfiguratsiyasi (advanced)

Worker `.env` dan `RABBITMQ_PREFETCH` o‘qiydi:
- `basic_qos` bilan parallel xabarni boshqaradi
- 10 soniyadan ortiq bo‘lsa avtomatik reconnect qiladi
- Har 1000 xabardan keyin `gc_collect_cycles()` chaqiriladi

---

## 🛠️ Supervisor bilan background ishga tushirish

`/etc/supervisor/conf.d/rabbit_worker.conf`:
```ini
[program:rabbit_worker]
command=php artisan rabbit:worker
autostart=true
autorestart=true
numprocs=2
stdout_logfile=/var/log/rabbit_worker.log
stderr_logfile=/var/log/rabbit_worker_error.log
```

---

## 🐳 Docker misoli

`docker-compose.yml`:
```yaml
version: "3.8"
services:
  app:
    build: .
    command: php artisan rabbit:worker
    depends_on:
      - rabbitmq
    environment:
      - RABBITMQ_HOST=rabbitmq
      - RABBITMQ_USER=muxtorov
      - RABBITMQ_PASS=5upris#1eWata2ped
    restart: always
  rabbitmq:
    image: rabbitmq:3-management
    ports:
      - "15672:15672"
      - "5672:5672"
```

---

## 🧠 Retry & Reconnect mexanizmi

| Mexanizm | Tavsif |
|-----------|---------|
| **Auto reconnect** | RabbitMQ bilan aloqa uzilsa, `Worker` o‘zi qayta ulanadi |
| **Retry publish** | `RabbitPublisher` xabarni 3 marta qayta yuboradi |
| **Confirm mode** | Rabbit serverdan ACK olgandan keyingina xabarni muvaffaqiyatli deb hisoblaydi |
| **Logging** | `/var/log/rabbit_worker_error.log` va `/var/log/rabbit_publisher_error.log` fayllariga yoziladi |

---

## 📈 Monitoring va Scaling

- Monitoring: `rabbitmqctl list_queues` yoki Prometheus RabbitMQ exporter
- Scaling: bir nechta worker containerlarini ishga tushiring
  ```bash
  docker-compose up --scale app=4
  ```

---

## 🧾 Lisensiya

MIT © [Muxtorov Tulqin](https://github.com/muxtorov98)

---

## ❤️ Hissa qo‘shish

Pull Requestlar, takliflar va yangi frameworklar integratsiyasi (masalan, **FrankenPHP**, **Slim**, **Lumen**) mamnuniyat bilan qabul qilinadi.
