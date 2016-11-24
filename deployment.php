#!/bin/bash

$(dirname $0)/vendor/bin/deployment $0
exit

<?php

require_once __DIR__ . "/vendor/autoload.php";

class Bootstrap extends PetrKnap\Web\Bootstrap
{
    /**
     * @inheritdoc
     */
    public static function isProduction()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    protected function getConfigFiles()
    {
        $configs = parent::getConfigFiles();
        array_unshift($configs, self::APP_DIR . "/config/deployment.neon");
        return $configs;
    }
}

$container = Bootstrap::getContainer();

$getIgnored = function(array $doNotUpload, array $uploadOnlyWebConfig) {
    $ignored = $doNotUpload;
    foreach ($uploadOnlyWebConfig as $item)
    {
        $ignored[] = "{$item}/*";
        $ignored[] = "!{$item}/.htaccess";
    }

    return $ignored;
};

$config = $container->getParameters()["deployment"];

return [
    "web" => [
        "remote" => "{$config["type"]}://{$config["user"]}:{$config["pass"]}@{$config["host"]}{$config["path"]}",
        "local" => __DIR__,
        "test" => false,
        "ignore" => $getIgnored(
            [
                "/*",
                "!/*/",
                "/.docker",
                "/.idea",
                "/.git*",
                "/app/config/config.local.neon",
                "/vendor/bin",
                "/vendor/dg/ftp-deployment", // development
                "/vendor/phpunit/*", // development
                "/tests"
            ],
            [
                "log",
                "temp"
            ]
        ),
        "allowDelete" => true,
        "purge" => [
            "temp/cache",
            "log/email-sent"
        ],
        "preprocess" => false,
        "after" => $config["after"]
    ],
    "tempDir" => Bootstrap::TEMP_DIR,
    "log" => Bootstrap::LOG_DIR . "/deployment.log",
    "colors" => true
];
