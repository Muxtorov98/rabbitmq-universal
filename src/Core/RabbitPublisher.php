<?php

namespace RabbitMQQueue\Core;

use PhpAmqpLib\Message\AMQPMessage;

class RabbitPublisher
{
    public function __construct(private RabbitMQConnection $connection) {}

    public function publish(string $queue, array $message): void
    {
        $ch = $this->connection->getChannel();
        $ch->queue_declare($queue, false, true, false, false);
        $ch->basic_publish(new AMQPMessage(json_encode($message)), '', $queue);

        echo "✅ Message sent to queue: {$queue}\n";
    }
}