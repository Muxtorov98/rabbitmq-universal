<?php

namespace RabbitMQQueue\Core;

interface QueueHandlerInterface
{
    public function handle(array $message): void;
}