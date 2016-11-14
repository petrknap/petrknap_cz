<?php

namespace PetrKnap\Web\Test;

use Nette\Application\IRouter;
use PetrKnap\Web\RouterFactory;

class RouterFactoryTest extends NetteTestCase
{
    public function testCreatesIRouterInstance()
    {
        $router = RouterFactory::createRouter(null, null);

        $this->assertInstanceOf(IRouter::class, $router);
    }
}
