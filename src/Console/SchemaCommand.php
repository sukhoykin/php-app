<?php

namespace App\Console;

use App\Controller;
use App\Database\Database;
use App\Interfaces\CommandInterface;
use App\Error\UsageError;
use Exception;

class SchemaCommand extends Controller implements CommandInterface
{
    const SCHEMA_PATH = __DIR__ . '/../../schema';

    const USAGE = "schema <command> [args]
  list                    list schemas and versions
  migrate                 init or migrate schema to last version
  drop <name> [--force]   drop schema";

    private function input($prompt)
    {
        echo $prompt;
        $fp = fopen("php://stdin", "r");
        return trim(fgets($fp));
    }

    public function run($args)
    {
        if ($args->has('help') || $args->count() < 1) {
            throw new UsageError(self::USAGE);
        }

        $command = $args->shift();

        switch ($command) {

            case 'list':
                $this->list();
                break;

            case 'migrate':
                $this->migrate();
                break;

            case 'drop':
                $this->drop($args);
                break;

            default:
                throw new \Exception('Invalid command: ' . $command);
        }
    }

    private function schemaConfig()
    {
        $path = SchemaCommand::SCHEMA_PATH . '/schema.php';

        if (!file_exists($path)) {
            throw new Exception('Schema config not found: ' . $path);
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
        echo 'Migrate: ', $schema, "\n";

        for ($i = $this->getSchemaVersion($schema) + 1; $i <= $version; $i++) {

            echo ' version: ', $i, "\n";

            $path = SchemaCommand::SCHEMA_PATH . '/' . $schema . '.' . $i . '.sql';
            $command = @file_get_contents($path);

            if ($command === false) {
                throw new \Exception('Could not read schema: ' . $path);
            }

            $database = $this->get(Database::class);
            $connection = $database->connection();

            $connection->beginTransaction();
            $connection->pdo()->exec($command);
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

        $database = $this->get(Database::class);
        $connection = $database->connection();

        $connection->prepare('CREATE TABLE IF NOT EXISTS ' . $table . ' (version int NOT NULL)');
        $connection->execute();

        $connection->prepare('SELECT version FROM ' . $table);
        $connection->execute();

        if ($row = $connection->fetch()) {
            $version = $row['version'];
        } else {

            $connection->prepare('INSERT INTO ' . $table . ' VALUES (?)');
            $connection->execute([$version]);
        }

        return $version;
    }

    private function setSchemaVersion($schema, $version)
    {
        $table = $this->schemaTable($schema);

        $database = $this->get(Database::class);
        $connection = $database->connection();

        $connection->prepare('UPDATE ' . $table . ' SET version = ?');
        $connection->execute([$version]);
    }

    private function drop($args)
    {
        if ($args->count() < 1) {
            throw new UsageError(self::USAGE, 1);
        }

        $name = $args->shift();

        if (!$args->has('--force')) {

            $confirm = $this->input('Are you sure you want to DROP schema? [Yes/No]: ');

            if ($confirm != 'Yes') {
                return;
            }
        }

        $path = SchemaCommand::SCHEMA_PATH . '/' . $name . '.drop.sql';
        $command = @file_get_contents($path);

        if ($command === false) {
            throw new Exception('Could not read schema: ' . $path);
        }

        $database = $this->get(Database::class);
        $connection = $database->connection();

        $connection->beginTransaction();
        $connection->pdo()->exec($command);
        $connection->commit();

        $connection->prepare('DROP TABLE IF EXISTS ' . $this->schemaTable($name));
        $connection->execute();
    }
}
