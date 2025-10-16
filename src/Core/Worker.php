<?php

namespace RabbitMQQueue\Core;

use PhpAmqpLib\Message\AMQPMessage;
use Throwable;

class Worker
{
    public function __construct(
        private RabbitMQConnection $connection,
        private HandlerRegistry $registry
    ) {}

    public function start(): void
    {
        $channel = $this->connection->getChannel();
        $handlers = $this->registry->getHandlers();

        foreach ($handlers as $queue => $handler) {
            $channel->queue_declare($queue, false, true, false, false);
            $channel->basic_consume($queue, '', false, false, false, false, function (AMQPMessage $msg) use ($handler, $queue) {
                $this->handleMessage($msg, $handler, $queue);
            });

            echo "👂 Listening on queue: {$queue}\n";
        }

        while ($channel->is_consuming()) {
            try {
                $channel->wait(null, false, 10);
            } catch (\PhpAmqpLib\Exception\AMQPTimeoutException) {
                // Timeout — worker ping or GC
                gc_collect_cycles();
            } catch (\PhpAmqpLib\Exception\AMQPIOException $e) {
                echo "⚠️  Lost connection: {$e->getMessage()}, reconnecting...\n";
                $this->connection->reconnect();
                $this->start(); // recursive restart
            }
        }
    }

    private function handleMessage(AMQPMessage $msg, $handler, string $queue): void
    {
        try {
            $body = json_decode($msg->body, true, 512, JSON_THROW_ON_ERROR);
            $handlerInstance = new $handler['class'](...$handler['dependencies']);

            $handlerInstance->handle($body);

            $msg->ack();
            echo "✅ Message processed from {$queue}\n";
        } catch (Throwable $e) {
            echo "❌ Error in queue {$queue}: {$e->getMessage()}\n";
            $msg->nack(false, false); // Reject without requeue
            file_put_contents('/var/log/rabbit_worker_error.log', date('Y-m-d H:i:s') . ' ' . $e->getMessage() . "\n", FILE_APPEND);
        }
    }
}