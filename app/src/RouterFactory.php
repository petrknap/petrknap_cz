<?php

namespace PetrKnap\Web;

use Nette\Application\IRouter;
use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;

class RouterFactory
{
    /**
     * @param string $homepage
     * @return IRouter
     */
    public static function createRouter($homepage)
    {
        $router = new RouteList();

        $router[] = new Route((Bootstrap::isProduction() ? "https://" : "http://") . "%sld%.%tld%/", [
            "presenter" => "ReverseProxy",
            "action" => "default",
            "url" => $homepage
        ]);

        return $router;
    }
}
