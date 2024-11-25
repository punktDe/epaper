<?php
namespace PunktDe\EPaper\AssetOperations;

/*
 *  (c) 2021 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */

use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\Media\Domain\Model\Asset;
use PunktDe\EPaper\Domain\EPaperManager;

class EPaperUploadOperation
{
    const EPAPER_NODETYPE = 'PunktDe.EPaper:Mixins.EPaperLink';

    /**
     * @param Asset $asset
     * @param NodeInterface $node
     */
    public static function rebuildCache(NodeInterface $node): void
    {
        if (!$node->getNodeType()->isOfType(self::EPAPER_NODETYPE) || !$node->getProperty('ePaper') instanceof Asset) {
            return;
        }

        try {
            $ePaperManager = new EPaperManager($node->getIdentifier(), $node);
            $ePaperManager->extractArchive($ePaperManager->findEPaperArchive());
        } catch (\Exception $e) {
        }
    }
}
