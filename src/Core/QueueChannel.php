<?php

namespace RabbitMQQueue\Core;

#[\Attribute(\Attribute::TARGET_CLASS)]
class QueueChannel
{
    public function __construct(public string $queueName) {}
}