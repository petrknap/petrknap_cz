<?php

namespace PetrKnap\Web;

use PetrKnap\Nette;

class Bootstrap extends Nette\Bootstrap\Bootstrap
{
    const APP_DIR = __DIR__ . "/..";
    const LOG_DIR = self::APP_DIR . "/../log";
    const TEMP_DIR = self::APP_DIR . "/../temp";

    /**
     * @return bool
     */
    public static function isProduction()
    {
        return !isset($_SERVER["SERVER_NAME"]) ?: strpos($_SERVER["SERVER_NAME"], ".dev") === false;
    }

    /**
     * @inheritdoc
     */
    protected function getDebugMode()
    {
        return !static::isProduction();
    }

    /**
     * @inheritdoc
     */
    protected function getAppDir()
    {
        return self::APP_DIR;
    }

    /**
     * @inheritdoc
     */
    protected function getLogDir()
    {
        return self::LOG_DIR;
    }

    /**
     * @inheritdoc
     */
    protected function getTempDir()
    {
        return self::TEMP_DIR;
    }

    /**
     * @inheritdoc
     */
    protected function getConfigFiles()
    {
        return [
            self::APP_DIR . "/config/config.neon",
            self::APP_DIR . "/config/config.local.neon"
        ];
    }
}
