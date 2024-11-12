<?php
namespace PunktDe\EPaper\Controller;

/*
 *  (c) 2021 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */

use Neos\Flow\Mvc\Controller\ActionController;
use Neos\Utility\MediaTypes;
use PunktDe\EPaper\Domain\EPaperManager;

class EPaperController extends ActionController
{
    /**
     * @param string $nodeIdentifier
     * @param string $filePath
     * @return string
     * @throws \Exception
     */
    public function serveAction(string $nodeIdentifier, string $filePath): string
    {
        $ePaperManager = new EPaperManager($nodeIdentifier);

        if (!$ePaperManager->extractedEPaperDirectoryExists()) {
            $asset = $ePaperManager->findEPaperArchive();
            $ePaperManager->extractArchive($asset);
        }

        $absoluteFilePath = $ePaperManager->resolveAbsoluteFilePath($filePath);
        if ($absoluteFilePath === '') {
            $this->response->setStatusCode(404);
            $this->response->setHttpHeader('X-Accel-Redirect', ''); // nginx will return 404 error page
            return '';
        }

        $this->response->setContentType(MediaTypes::getMediaTypeFromFilename(basename($absoluteFilePath)));
        $this->response->setHttpHeader('X-Accel-Redirect', substr(realpath($absoluteFilePath), strpos($absoluteFilePath, '/Data/')));
        return '';
    }
}
