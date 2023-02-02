<?php
declare(strict_types=1);

namespace PunktDe\EPaper\ArchiveExtractor;

/*
 *  (c) 2021 punkt.de GmbH - Karlsruhe, Germany - https://punkt.de
 *  All rights reserved.
 *
 *  Zip archive extractor for zips from https://www.1000grad-epaper.de/
 */

use Neos\Flow\ResourceManagement\Exception;
use Neos\Media\Domain\Model\AssetInterface;
use Neos\Utility\Exception\FilesException;
use Psr\Log\LoggerInterface;

interface ArchiveExtractorInterface
{

    /**
     * @param AssetInterface $asset
     * @param string $ePaperDirectory
     * @return void
     * @throws Exception
     */
    public function extractArchive(AssetInterface $asset, string $ePaperDirectory): void;

    /**
     * @param LoggerInterface $logger
     * @return void
     */
    public function setLogger (LoggerInterface $logger): void;
}
