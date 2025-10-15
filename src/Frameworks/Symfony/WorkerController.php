<?php

namespace RabbitMQQueue\Frameworks\Symfony;

use RabbitMQQueue\Core\{EnvLoader, RabbitMQConnection, HandlerRegistry, Worker};
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RabbitWorkerCommand extends Command
{
    protected static $defaultName = 'rabbit:worker:start';

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('🚀 Loading environment...');
        EnvLoader::load(__DIR__ . '/../../../');

        $connection = new RabbitMQConnection();
        $connection->connect();

        $path = $_ENV['HANDLER_PATH'] ?? __DIR__ . '/../../Handlers';
        $registry = new HandlerRegistry($path);

        $output->writeln('👷 Worker started...');
        (new Worker($connection, $registry))->start();

        return Command::SUCCESS;
    }
}