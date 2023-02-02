<?php
namespace PunktDe\EPaper\Domain;

/*
 *  (c) 2021 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */

use Neos\Flow\Annotations as Flow;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\ContentRepository\Exception\NodeException;
use Neos\Flow\Log\Utility\LogEnvironment;
use Neos\Flow\ResourceManagement\Exception;
use Neos\Flow\Session\SessionInterface;
use Neos\Media\Domain\Model\AssetInterface;
use Neos\Neos\Controller\CreateContentContextTrait;
use Neos\Utility\Exception\FilesException;
use Neos\Utility\Files;
use Psr\Log\LoggerInterface;
use PunktDe\EPaper\ArchiveExtractor\ArchiveExtractorInterface;
use PunktDe\EPaper\Exceptions\NodeHasNoEPaperArchiveException;
use PunktDe\EPaper\Exceptions\NodeNotFoundException;

class EPaperManager
{
    use CreateContentContextTrait;

    /**
     * @Flow\InjectConfiguration(path="extractedEPaperCacheDir")
     * @var string
     */
    protected $extractedPaperCacheDir;

    /**
     * @var LoggerInterface
     * @Flow\Inject
     */
    protected $logger;

    /**
     * @var string
     */
    protected $nodeIdentifier;

    /**
     * @var NodeInterface
     */
    protected $ePaperLinkNode;

    /**
     * @var string
     */
    protected $ePaperDirectory;

    /**
     * @var SessionInterface
     * @Flow\Inject
     */
    protected $session;

    /**
     * @var ArchiveExtractorInterface
     * @Flow\Inject 
     */
    protected $archiveExtractor;

    public function __construct(string $nodeIdentifier = '', ?NodeInterface $ePaperLinkNode = null)
    {
        $this->nodeIdentifier = $nodeIdentifier;
        $this->ePaperLinkNode = $ePaperLinkNode;
    }


    /**
     * @throws FilesException
     * @throws NodeHasNoEPaperArchiveException
     * @throws NodeNotFoundException
     * @throws \Exception
     * @throws Exception
     * @return void
     */
    public function initializeObject(): void
    {
        $this->ePaperDirectory = Files::concatenatePaths([$this->extractedPaperCacheDir, $this->nodeIdentifier]);
        $this->archiveExtractor->setLogger($this->logger);

        if (!$this->extractedEPaperDirectoryExists()) {
            $asset = $this->findEPaperArchive();
            $this->extractArchive($asset);
        }

//        $this->checkMapAccess();
    }

//    /**
//     * @return bool
//     * @throws AccessDeniedException
//     * @throws NodeNotFoundException
//     * @throws \Exception
//     */
//    private function checkMapAccess(): bool
//    {
//        $ePaperAccessKey = 'ePaperAccess_' . $this->nodeIdentifier;
//        $accessible = null;
//
//        if ($this->session->isStarted() === false) {
//            $this->session->start();
//        }
//
//        if ($this->session->hasKey($ePaperAccessKey)) {
//            $accessible = $this->session->getData($ePaperAccessKey);
//        }
//
//        if ($accessible === null) {
//            $accessible = (new SecurableContentService())->isContentAccessible($this->getEPaperLinkNode());
//            $this->session->putData($ePaperAccessKey, $accessible);
//        }
//
//        if ($accessible !== true) {
//            throw new AccessDeniedException('Access to requested publication was denied', 1526144111);
//        }
//
//        return true;
//    }

    /**
     * @param string $filePath
     * @return string
     * @throws \Exception
     */
    public function resolveAbsoluteFilePath(string $filePath): string
    {
        $absolutePath = '';
        $pathCandidate = Files::concatenatePaths([realpath($this->ePaperDirectory), $filePath]);

        if (file_exists($pathCandidate) && is_file($pathCandidate)) {
            $absolutePath = $pathCandidate;
        } else {
            $pathCandidateWithIndexFile = Files::concatenatePaths([$pathCandidate, 'index.html']);
            if (file_exists($pathCandidateWithIndexFile)) {
                $absolutePath = $pathCandidateWithIndexFile;
            }
        }

        if (realpath($absolutePath) !== $absolutePath) {
            $this->logger->warning(sprintf('Realpath of the path "%s" differs, could be a path traversal attack. An empty path was returned.', $absolutePath), LogEnvironment::fromMethodName(__METHOD__));
            return '';
        }

        return $absolutePath;
    }

    /**
     * @return bool
     */
    protected function extractedEPaperDirectoryExists(): bool
    {
        return file_exists(Files::concatenatePaths([$this->ePaperDirectory, 'index.html']));
    }

    /**
     * @return AssetInterface
     * @throws NodeHasNoEPaperArchiveException
     * @throws NodeNotFoundException
     * @throws NodeException
     */
    public function findEPaperArchive(): AssetInterface
    {
        $linkNode = $this->getEPaperLinkNode();

        if (!$linkNode->hasProperty('ePaper') || !($linkNode->getProperty('ePaper') instanceof AssetInterface)) {
            throw new NodeHasNoEPaperArchiveException(sprintf('No e-paper archive was referenced in EPaperLink Element %s', $linkNode->getIdentifier()), 1624373833);
        }

        return $linkNode->getProperty('ePaper');
    }

    /**
     * @param AssetInterface $asset
     * @throws Exception
     * @throws FilesException
     * @return void
     */
    public function extractArchive(AssetInterface $asset): void
    {
        $this->archiveExtractor->extractArchive($asset, $this->ePaperDirectory);
    }

    /**
     * @return NodeInterface
     * @throws NodeNotFoundException
     */
    protected function getEPaperLinkNode(): NodeInterface
    {
        if (!($this->ePaperLinkNode instanceof NodeInterface)) {
            $this->ePaperLinkNode = $this->createContentContext('live')->getNodeByIdentifier($this->nodeIdentifier);

            if (!($this->ePaperLinkNode instanceof NodeInterface)) {
                throw new NodeNotFoundException(sprintf('Node with identifier %s was not found', $this->nodeIdentifier), 1526069762);
            }
        }

        return $this->ePaperLinkNode;
    }
}
