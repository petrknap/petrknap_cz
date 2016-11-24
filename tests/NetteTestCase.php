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
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::getContainer()->addService(
            Connection::class,
            (new static())->getDatabaseConnection()
        );
    }

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        parent::setUp();

        $this->refillDatabase($this->getDatabaseConnection());
    }
    
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
            $this->refillDatabase($databaseConnection);
        }

        return $databaseConnection;
    }

    /**
     * @param Connection $connection
     * @return void
     */
    protected function refillDatabase(/** @noinspection PhpUnusedParameterInspection */$connection)
    {
        return /* void */;
    }
}
