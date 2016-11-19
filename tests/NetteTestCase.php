<?php

namespace PetrKnap\Web\Test;

use Nette\Database\Connection;
use PetrKnap\Nette\Bootstrap\PhpUnit;
use PetrKnap\Web\Service\MigrationService;

class NetteTestCase extends PhpUnit\NetteTestCase
{
    const NETTE_BOOTSTRAP_CLASS = NetteBootstrap::class;

    /**
     * @inheritdoc
     */
    protected function clearTempExcludedFiles()
    {
        return [".gitignore", ".htaccess"];
    }

    /**
     * @return Connection
     */
    protected function getDatabaseConnection()
    {
        static $databaseConnection;

        if (!$databaseConnection) {
            $databaseConnection = self::getContainer()->getByType(Connection::class);
            (new MigrationService($databaseConnection))->migrate();
            $this->fillDatabase($databaseConnection);
        }

        return $databaseConnection;
    }

    /**
     * @param Connection $connection
     * @return void
     */
    protected function fillDatabase(/** @noinspection PhpUnusedParameterInspection */ $connection)
    {
        return /* void */;
    }
}
