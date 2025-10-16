<?php

namespace RabbitMQQueue\Core;

use PhpAmqpLib\Message\AMQPMessage;

class RabbitPublisher
{
    private RabbitMQConnection $connection;
    private int $maxRetries = 3;
    private int $retryDelay = 3;

    public function __construct(?RabbitMQConnection $connection = null)
    {
        $this->connection = $connection ?? new RabbitMQConnection();
        if (!$connection) $this->connection->connect();
    }

    public function publish(string $queue, array $data): void
    {
        $attempt = 0;
        $channel = $this->connection->getChannel();

        while ($attempt < $this->maxRetries) {
            try {
                $channel->queue_declare($queue, false, true, false, false);
                $message = new AMQPMessage(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR), [
                    'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
                ]);

                $channel->confirm_select(); // enables confirm mode
                $channel->basic_publish($message, '', $queue);
                $channel->wait_for_pending_acks_returns();

                echo "📤 Sent to [{$queue}]: " . json_encode($data, JSON_UNESCAPED_UNICODE) . "\n";
                return;
            } catch (\Throwable $e) {
                $attempt++;
                echo "⚠️  Publish attempt {$attempt}/{$this->maxRetries} failed: {$e->getMessage()}\n";
                sleep($this->retryDelay);
                if ($attempt >= $this->maxRetries) {
                    file_put_contents('/var/log/rabbit_publisher_error.log', date('Y-m-d H:i:s') . ' ' . $e->getMessage() . "\n", FILE_APPEND);
                }
            }
        }
    }
}