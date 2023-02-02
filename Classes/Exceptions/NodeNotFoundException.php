<?php
namespace PunktDe\EPaper\Exceptions;

/*
 *  (c) 2021 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */

use \Neos\Flow\Mvc\Exception;

class NodeNotFoundException extends Exception
{
    /**
     * @var integer
     */
    protected $statusCode = 404;
}
