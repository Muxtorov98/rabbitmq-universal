<?php

namespace RabbitMQQueue\Core;

use PhpAmqpLib\Message\AMQPMessage;

class Worker
{
    public function __construct(
        private RabbitMQConnection $connection,
        private HandlerRegistry $registry
    ) {}

    public function start(): void
    {
        $channel = $this->connection->getChannel();

        foreach ($this->registry->getQueueList() as $queue) {
            $handler = $this->registry->getHandler($queue);
            $channel->queue_declare($queue, false, true, false, false);

            $channel->basic_consume($queue, '', false, true, false, false, function (AMQPMessage $msg) use ($handler) {
                $data = json_decode($msg->body, true);
                $handler?->handle($data);
            });

            echo "👷 Listening on queue: {$queue}\n";
        }

        while ($channel->is_consuming()) {
            $channel->wait();
        }
    }
}