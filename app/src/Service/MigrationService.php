<?php

namespace PetrKnap\Web\Service;

use Nette\Database\Connection;
use PetrKnap\Php\MigrationTool\SqlMigrationTool;
use PetrKnap\Web\Bootstrap;

class MigrationService extends SqlMigrationTool
{
    /**
     * @var \PDO
     */
    private $pdo;

    public function __construct(Connection $connection)
    {
        $this->pdo = $connection->getPdo();
    }

    /**
     * @inheritdoc
     */
    protected function getPathToDirectoryWithMigrationFiles()
    {
        return Bootstrap::APP_DIR . "/migration";
    }

    /**
     * @inheritdoc
     */
    protected function getPhpDataObject()
    {
        return $this->pdo;
    }

    /**
     * @inheritdoc
     */
    protected function getNameOfMigrationTable()
    {
        return "migrations";
    }
}
