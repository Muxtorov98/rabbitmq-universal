<?php

namespace RabbitMQQueue\Core;

use PhpAmqpLib\Message\AMQPMessage;

class RabbitPublisher
{
    private RabbitMQConnection $connection;

    public function __construct()
    {
        EnvLoader::load(dirname(__DIR__, 2));

        $this->connection = new RabbitMQConnection();
        $this->connection->connect();
    }

    /**
     * Queue ga xabar yuborish
     */
    public function publish(string $queue, array $data): void
    {
        $channel = $this->connection->getChannel();

        // Queue mavjud bo‘lmasa — yaratish
        $channel->queue_declare(
            $queue,
            false,  // passive
            true,   // durable
            false,  // exclusive
            false   // auto_delete
        );

        // JSON xabar tayyorlash
        $payload = json_encode($data, JSON_UNESCAPED_UNICODE);
        $message = new AMQPMessage(
            $payload,
            ['content_type' => 'application/json', 'delivery_mode' => 2]
        );

        // Queue ga yuborish
        $channel->basic_publish($message, '', $queue);

        echo "✅ Message published to queue: {$queue}\n";

        // Kanalni yopish
        $channel->close();
        $this->connection->close();
    }
}