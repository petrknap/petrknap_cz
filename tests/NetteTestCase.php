<?php

namespace PetrKnap\Web\Test;

use PetrKnap\Nette\Bootstrap\PhpUnit;

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
}
