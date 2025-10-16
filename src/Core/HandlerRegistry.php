<?php

namespace RabbitMQQueue\Core;

use ReflectionClass;

class HandlerRegistry
{
    /** @var QueueHandlerInterface[] */
    private array $handlers = [];

    public function __construct(string $handlersPath)
    {
        foreach ($this->discoverHandlers($handlersPath) as $handler) {
            $ref = new ReflectionClass($handler);
            $attrs = $ref->getAttributes(QueueChannel::class);

            if ($attrs) {
                $queueName = $attrs[0]->newInstance()->queueName;
                $this->handlers[$queueName] = $handler;
            }
        }
    }

    private function discoverHandlers(string $dir): array
    {
        $found = [];

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if (str_ends_with($file->getFilename(), 'Handler.php')) {
                $content = file_get_contents($file);
                preg_match('/namespace\s+([^;]+);/', $content, $ns);
                preg_match('/class\s+(\w+)/', $content, $class);

                if (!empty($class[1])) {
                    $fullClass = trim(($ns[1] ?? '') . '\\' . $class[1], '\\');
                    if (class_exists($fullClass) &&
                        is_subclass_of($fullClass, QueueHandlerInterface::class)) {
                        $found[] = new $fullClass();
                    }
                }
            }
        }

        return $found;
    }

    public function getHandler(string $queue): ?QueueHandlerInterface
    {
        return $this->handlers[$queue] ?? null;
    }

    public function getQueueList(): array
    {
        return array_keys($this->handlers);
    }
}