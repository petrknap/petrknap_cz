<?php

namespace PetrKnap\Web;

use Nette\Application\IRouter;
use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;

class RouterFactory
{
    /**
     * @param string $homepage
     * @param string $primarySecondLevelDomain
     * @return IRouter
     */
    public static function createRouter($homepage, $primarySecondLevelDomain)
    {
        $protocol = Bootstrap::isProduction() ? "https://" : "http://";

        $router = new RouteList();

        $router[] = new Route("{$protocol}api.%sld%.%tld%/<action>/?sk=<secret_key>", [
            "presenter" => "Api"
        ]);
        $router[] = new Route("{$protocol}{$primarySecondLevelDomain}.%tld%/", [
            "presenter" => "ReverseProxy",
            "action" => "default",
            "url" => $homepage
        ]);

        return $router;
    }
}
