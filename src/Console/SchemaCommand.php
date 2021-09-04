<?php

namespace Sukhoykin\App\Console;

use Sukhoykin\App\Interfaces\Configurable;
use Sukhoykin\App\Interfaces\Executable;
use Sukhoykin\App\Interfaces\Service;

use Psr\Container\ContainerInterface;
use Sukhoykin\App\Config\Section;
use Sukhoykin\App\Mapper\Datasource;

use Sukhoykin\App\Console\Arguments;
use Sukhoykin\App\Console\UsageError;
use Exception;
use Psr\Log\LoggerInterface;

class SchemaCommand implements Configurable, Executable, Service
{
    const CONFIG = 'schema';

    private $datasource, $log;
    private $path;

    const USAGE = "schema <command> [args]
  list                      list schemas and versions
  migrate                   init or migrate schema to last version
  drop <schema> [--force]   drop schema";

    private function input($prompt)
    {
        echo $prompt;
        $fp = fopen("php://stdin", "r");
        return trim(fgets($fp));
    }

    public function setRegistry(ContainerInterface $registry)
    {
        $this->datasource = $registry->get(Datasource::class);
        $this->log = $registry->get(LoggerInterface::class);
    }

    public function getName(): string
    {
        return 'schema';
    }

    public function getUsage(): string
    {
        return self::USAGE;
    }

    public function getDescription(): string
    {
        return 'Database Schema Migration';
    }

    public function configurate(Section $config)
    {
        $this->path = $config->getString('path');
    }

    public function execute(Arguments $arguments)
    {
        if ($arguments->has('help') || $arguments->count() < 1) {
            $this->log->info(sprintf("%s\nUsage: %s", $this->getDescription(), $this->getUsage()));
            return;
        }

        $command = $arguments->shift();

        switch ($command) {

            case 'list':
                $this->list();
                break;

            case 'migrate':
                $this->migrate();
                break;

            case 'drop':
                $this->drop($arguments);
                break;

            default:
                throw new UsageError("Invalid command '$command'", $this);
        }
    }

    private function schemaConfig()
    {
        $path = $this->path . '/schema.php';

        if (!file_exists($path)) {
            throw new Exception("Schema config not found: $path");
        }

        return include $path;
    }

    private function list()
    {
        $config = $this->schemaConfig();

        foreach ($config as $schema => $version) {
            echo '  ', $schema, ': ', $version, ' (', $this->getSchemaVersion($schema), ')',  "\n";
        }
    }

    private function migrate()
    {
        $config = $this->schemaConfig();

        foreach ($config as $schema => $version) {
            $this->migrateSchema($schema, $version);
        }
    }

    private function migrateSchema($schema, $version)
    {
        $this->log->info("Migrate '$schema'");

        for ($i = $this->getSchemaVersion($schema) + 1; $i <= $version; $i++) {

            $this->log->info("  version '$i'");

            $path = $this->path . '/' . $schema . '.' . $i . '.sql';
            $command = @file_get_contents($path);

            if ($command === false) {
                throw new \Exception("Could not read schema: $path");
            }

            $connection = $this->datasource->getConnection();

            $connection->beginTransaction();
            $connection->exec($command);
            $this->setSchemaVersion($schema, $i);
            $connection->commit();
        }
    }

    private function schemaTable($schema)
    {
        if ($schema == 'public') {
            return '"schema_version"';
        } else {
            return '"' . $schema . '"."schema_version"';
        }
    }

    private function getSchemaVersion($schema)
    {
        $table = $this->schemaTable($schema);
        $version = 0;

        $connection = $this->datasource->getConnection();

        $ps = $connection->prepare('CREATE TABLE IF NOT EXISTS ' . $table . ' (version int NOT NULL)');
        $ps->execute();

        $ps = $connection->prepare('SELECT version FROM ' . $table);
        $ps->execute();

        if ($row = $ps->fetch()) {
            $version = $row['version'];
        } else {

            $ps = $connection->prepare('INSERT INTO ' . $table . ' VALUES (?)');
            $ps->execute([$version]);
        }

        return $version;
    }

    private function setSchemaVersion($schema, $version)
    {
        $table = $this->schemaTable($schema);

        $connection = $this->datasource->getConnection();

        $ps = $connection->prepare('UPDATE ' . $table . ' SET version = ?');
        $ps->execute([$version]);
    }

    private function drop(Arguments $args)
    {
        $schema = $args->shift();

        if (!$schema) {
            throw new UsageError('Schema required', $this);
        }

        if (!$args->has('--force')) {

            $confirm = $this->input('Are you sure you want to DROP schema? [Yes/No]: ');

            if ($confirm != 'Yes') {
                return;
            }
        }

        $path = $this->path . '/' . $schema . '.drop.sql';
        $command = @file_get_contents($path);

        if ($command === false) {
            throw new Exception("Could not read schema: $path");
        }

        $connection = $this->datasource->getConnection();

        $connection->beginTransaction();
        $connection->exec($command);
        $connection->commit();

        $ps = $connection->prepare('DROP TABLE IF EXISTS ' . $this->schemaTable($schema));
        $ps->execute();
    }
}
