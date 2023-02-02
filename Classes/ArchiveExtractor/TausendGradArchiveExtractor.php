<?php
declare(strict_types=1);

namespace PunktDe\EPaper\ArchiveExtractor;

/*
 *  (c) 2021 punkt.de GmbH - Karlsruhe, Germany - https://punkt.de
 *  All rights reserved.
 *
 *  Zip archive extractor for zips from https://www.1000grad-epaper.de/
 */

use Neos\Flow\Log\Utility\LogEnvironment;
use Neos\Flow\ResourceManagement\Exception;
use Neos\Media\Domain\Model\AssetInterface;
use Neos\Utility\Exception\FilesException;
use Neos\Utility\Files;
use Psr\Log\LoggerInterface;

class TausendGradArchiveExtractor implements ArchiveExtractorInterface
{
    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * @param AssetInterface $asset
     * @param string $ePaperDirectory
     * @return void
     * @throws Exception
     * @throws FilesException
     */
    public function extractArchive(AssetInterface $asset, string $ePaperDirectory): void
    {
        if (is_dir($ePaperDirectory)) {
            $this->logger->info('Removing existing EPaperDirectory ' . $ePaperDirectory, LogEnvironment::fromMethodName(__METHOD__));
            Files::removeDirectoryRecursively($ePaperDirectory);
        }
        $zip = new \ZipArchive();
        $zip->open($asset->getResource()->createTemporaryLocalCopy());
        $zip->extractTo($ePaperDirectory);
        $zip->close();

        $this->logger->info(sprintf('EPaperArchive %s was extracted to directory %s', $asset->getResource()->getFilename(), $ePaperDirectory), LogEnvironment::fromMethodName(__METHOD__));

        // In case the complete folder was ziped we only have one directory and
        // no files in there. In this case we need to elevate the sub directories
        $dirContents = array_diff(scandir($ePaperDirectory), ['..', '.', '__MACOSX']);
        $absolutePathToZipDirectory = Files::concatenatePaths([$ePaperDirectory, current($dirContents)]);
        if (count($dirContents) === 1 && is_dir($absolutePathToZipDirectory)) {
            $this->logger->info(sprintf('Found only one folder %s in the extracted zip directory, moving the contents one level up ', $absolutePathToZipDirectory), LogEnvironment::fromMethodName(__METHOD__));
            Files::copyDirectoryRecursively($absolutePathToZipDirectory, $ePaperDirectory);
            Files::removeDirectoryRecursively($absolutePathToZipDirectory);

            foreach (Files::readDirectoryRecursively($ePaperDirectory) as $file) {
                if (strpos($file, '.mepa')) {
                    unlink(str_replace('.mepa', '.zip', $file));
                    unlink($file);
                }
            }

            $zip = new \ZipArchive();
            $zip->open(Files::readDirectoryRecursively($ePaperDirectory)[0]);
            $zip->extractTo($ePaperDirectory);
            $zip->close();
            unlink(Files::readDirectoryRecursively($ePaperDirectory)[0]);
        }
    }
}
