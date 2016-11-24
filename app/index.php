<?php

use Netpromotion\Profiler\Adapter\PsrLoggerAdapter;
use Netpromotion\Profiler\Profiler;
use Netpromotion\TracyPsrLogger\TracyPsrLogger;
use Nette\Application\Application;
use PetrKnap\Web\Bootstrap;

require __DIR__ . "/../vendor/autoload.php";

if (!Bootstrap::isProduction()) {
    Profiler::enable();
}/* else {
    Profiler::enable();
}*/

Profiler::start("Application run");
Bootstrap::getContainer()->getByType(Application::class)->run();
Profiler::finish("Application run");

if (Profiler::isEnabled()) {
    (new PsrLoggerAdapter(new TracyPsrLogger()))->log();
}
