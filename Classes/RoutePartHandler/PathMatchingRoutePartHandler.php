<?php
namespace PunktDe\EPaper\RoutePartHandler;

/*
 *  (c) 2021 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */

use Neos\Flow\Mvc\Routing\DynamicRoutePart;

class PathMatchingRoutePartHandler extends DynamicRoutePart
{
    /**
     * @param string $routePath
     * @return string
     */
    protected function findValueToMatch($routePath)
    {
        return $routePath;
    }
}
