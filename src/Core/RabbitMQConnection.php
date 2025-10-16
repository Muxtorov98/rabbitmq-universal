<?php

namespace RabbitMQQueue\Core;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exception\AMQPIOException;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitMQConnection
{
    private ?AMQPStreamConnection $connection = null;
    private ?\PhpAmqpLib\Channel\AMQPChannel $channel = null;

    private int $reconnectDelay = 5; // sekund
    private int $maxReconnectAttempts = 10;

    public function connect(): void
    {
        $host = $_ENV['RABBITMQ_HOST'] ?? '127.0.0.1';
        $port = $_ENV['RABBITMQ_PORT'] ?? 5672;
        $user = $_ENV['RABBITMQ_USER'] ?? 'guest';
        $pass = $_ENV['RABBITMQ_PASS'] ?? 'guest';
        $vhost = $_ENV['RABBITMQ_VHOST'] ?? '/';
        $prefetch = $_ENV['RABBITMQ_PREFETCH'] ?? 10;
        $ssl = ($_ENV['RABBITMQ_SSL'] ?? 'false') === 'true';

        $attempt = 0;
        while ($attempt < $this->maxReconnectAttempts) {
            try {
                if ($ssl) {
                    $this->connection = AMQPStreamConnection::create_connection(
                        [['host' => $host, 'port' => $port, 'user' => $user, 'password' => $pass, 'vhost' => $vhost]],
                        ['insist' => false, 'login_method' => 'AMQPLAIN', 'locale' => 'en_US', 'connection_timeout' => 5.0, 'read_write_timeout' => 5.0, 'ssl_protocol' => 'tls']
                    );
                } else {
                    $this->connection = new AMQPStreamConnection($host, $port, $user, $pass, $vhost);
                }

                $this->channel = $this->connection->channel();
                $this->channel->basic_qos(null, $prefetch, null);

                echo "✅ Connected to RabbitMQ at {$host}:{$port} (vhost: {$vhost})\n";
                return;
            } catch (\Exception $e) {
                $attempt++;
                echo "⚠️  RabbitMQ connection failed ({$attempt}/{$this->maxReconnectAttempts}): {$e->getMessage()}\n";
                sleep($this->reconnectDelay);
            }
        }

        throw new \RuntimeException("❌ Failed to connect to RabbitMQ after {$this->maxReconnectAttempts} attempts.");
    }

    public function getChannel(): \PhpAmqpLib\Channel\AMQPChannel
    {
        return $this->channel;
    }

    public function getConnection(): AMQPStreamConnection
    {
        return $this->connection;
    }

    public function reconnect(): void
    {
        echo "🔁 Reconnecting to RabbitMQ...\n";
        $this->close();
        $this->connect();
    }

    public function close(): void
    {
        if ($this->channel && $this->channel->is_open()) {
            $this->channel->close();
        }
        if ($this->connection && $this->connection->isConnected()) {
            $this->connection->close();
        }
    }

    public function __destruct()
    {
        $this->close();
    }
}