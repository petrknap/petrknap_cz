<?php

namespace PetrKnap\Web;

use Nette\Application\IRouter;
use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;

class RouterFactory
{
    /**
     * @param string[] $domains
     * @return IRouter
     */
    public static function createRouter(array $domains)
    {
        $protocol = Bootstrap::isProduction() ? "https://" : "http://";

        $router = new RouteList();

        $router[] = new Route("{$protocol}{$domains["primary"]}/", [
            "presenter" => "ReverseProxy",
            "action" => "homepage"
        ]);
        $router[] = new Route("http://{$domains["link"]}/to/<keyword .*>.<extension [^/]*>", [
            "presenter" => "ReverseProxy",
            "action" => "byKeyword"
        ]);
        $router[] = new Route("http://{$domains["link"]}/to/<keyword .*>/", [
            "presenter" => "ReverseProxy",
            "action" => "byKeyword"
        ]);
        $router[] = new Route("{$protocol}link.{$domains["primary"]}/to/<keyword .*>.<extension [^/]*>", [
            "presenter" => "ReverseProxy",
            "action" => "byKeyword"
        ]);
        $router[] = new Route("{$protocol}link.{$domains["primary"]}/to/<keyword .*>/", [
            "presenter" => "ReverseProxy",
            "action" => "byKeyword"
        ]);
        $router[] = new Route("{$protocol}api.{$domains["primary"]}/<action>/?sk=<secret_key>", [
            "presenter" => "Api"
        ]);

        return $router;
    }
}
