<?php

use Nette\Application\Application;
use PetrKnap\Web\Bootstrap;

require __DIR__ . "/../vendor/autoload.php";

Bootstrap::getContainer()->getByType(Application::class)->run();
