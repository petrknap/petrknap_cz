<?php

namespace PetrKnap\Web\Test;

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
     * @inheritdoc
     */
    public function setUp()
    {
        parent::setUp();

        self::getContainer()->getByType(MigrationService::class)->migrate();
    }
}
