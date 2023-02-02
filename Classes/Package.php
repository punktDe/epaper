<?php
namespace PunktDe\EPaper;

/*
 *  (c) 2021 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */

use Neos\Flow\Core\Bootstrap;
use Neos\Flow\Package\Package as BasePackage;
use Neos\Neos\Controller\Backend\ContentController;
use PunktDe\EPaper\AssetOperations\EPaperUploadOperation;

class Package extends BasePackage
{

    /**
     * @param Bootstrap $bootstrap The current bootstrap
     * @return void
     */
    public function boot(Bootstrap $bootstrap)
    {
        $dispatcher = $bootstrap->getSignalSlotDispatcher();

        $dispatcher->connect(ContentController::class, 'assetUploaded', EPaperUploadOperation::class, '::rebuildCache');
    }
}
