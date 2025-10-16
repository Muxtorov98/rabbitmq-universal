<?php

namespace RabbitMQQueue\Core;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;

class HandlerRegistry
{
    private array $handlers = [];

    public function __construct(string $path)
    {
        $this->discoverHandlers($path);
    }

    private function discoverHandlers(string $path): void
    {
        // Path aliaslarni aniqlaymiz
        if (class_exists('\Yii') && str_starts_with($path, '@')) {
            $path = \Yii::getAlias($path);
        }

        $realPath = realpath($path);
        if (!$realPath || !is_dir($realPath)) {
            throw new \RuntimeException("❌ Handler path topilmadi: {$path}");
        }

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($realPath));

        foreach ($iterator as $file) {
            if ($file->isDir() || $file->getExtension() !== 'php') continue;

            $content = file_get_contents($file->getRealPath());
            if (preg_match('/namespace\s+([a-zA-Z0-9_\\\\]+)/', $content, $namespaceMatch) &&
                preg_match('/class\s+([a-zA-Z0-9_]+)/', $content, $classMatch)) {

                $className = $namespaceMatch[1] . '\\' . $classMatch[1];

                if (!class_exists($className)) {
                    require_once $file->getRealPath();
                }

                if (class_exists($className)) {
                    $reflection = new ReflectionClass($className);
                    if ($reflection->implementsInterface(QueueHandlerInterface::class)) {
                        $attrs = $reflection->getAttributes(QueueChannel::class);
                        if ($attrs) {
                            $queue = $attrs[0]->newInstance()->queue;
                            $this->handlers[$queue] = [
                                'class' => $className,
                                'dependencies' => [] // constructor paramlar uchun kelajakda kengaytiriladi
                            ];
                        }
                    }
                }
            }
        }

        if (empty($this->handlers)) {
            echo "⚠️  Handler topilmadi ({$path})\n";
        }
    }

    public function getHandlers(): array
    {
        return $this->handlers;
    }
}