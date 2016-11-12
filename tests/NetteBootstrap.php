<?php

namespace PetrKnap\Web\Test;

use PetrKnap\Web\Bootstrap;

class NetteBootstrap extends Bootstrap
{
    /**
     * @inheritdoc
     */
    public static function isProduction()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    protected function getConfigFiles()
    {
        return array_merge(parent::getConfigFiles(), []);
    }
}
