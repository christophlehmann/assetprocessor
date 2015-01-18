<?php
namespace Lemming\AssetProcessor\Command;

use TYPO3\Flow\Annotations as Flow;

/**
 * Controller for processing assets already in the system
 */
class AssetProcessorCommandController extends \TYPO3\Flow\Cli\CommandController {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Media\Domain\Repository\AssetRepository
	 */
	protected $assetRepository;

	/**
	 * @Flow\Inject
	 * @var \Lemming\AssetProcessor\Service\AssetProcessService
	 */
	protected $assetProcessService;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Resource\ResourceManager
	 */
	protected $resourceManager;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Utility\Environment
	 */
	protected $environment;

	/**
	 * Process assets
	 *
	 * @throws \Exception
	 */
	public function runCommand() {
		$temporaryDirectory = $this->environment->getPathToTemporaryDirectory() . 'Lemming.AssetProcessor';
		\TYPO3\Flow\Utility\Files::createDirectoryRecursively($temporaryDirectory);

		$assets = $this->assetRepository->findAll();

		if ($this->resourceFilesAvailable($assets) === FALSE) {
			$this->outputLine('Resource files could not be found. Fix it first');
			$this->sendAndExit(1);
		}

		$oldResources = array();
		/** @var \TYPO3\Media\Domain\Model\Asset $asset */
		foreach ($assets as $asset) {
			$resourceFile = $asset->getResource()->getUri();
			$resourceFileName = $asset->getResource()->getFilename();

			$temporaryFile = $temporaryDirectory . '/' . $resourceFileName;
			if (copy($resourceFile, $temporaryFile) === FALSE) {
				throw new \Exception('Could not copy ' . $resourceFile . ' to ' . $temporaryDirectory);
			}

			$mediaType = \TYPO3\Flow\Utility\MediaTypes::getMediaTypeFromFilename($resourceFileName);
			$this->assetProcessService->processFileByMediaType($temporaryFile, $mediaType);

			$oldHash = $asset->getResource()->getResourcePointer()->getHash();
			$newHash = sha1_file($temporaryFile);
			if ($oldHash !== $newHash) {
				array_push($oldResources,$asset->getResource());
				$newResource = $this->resourceManager->importResource($temporaryFile);
				$newResource->setPublishingConfiguration($asset->getResource()->getPublishingConfiguration());
				$asset->setResource($newResource);
				$this->assetRepository->update($asset);
			}

			unlink($temporaryFile);
		}

		foreach($oldResources as $resource) {
			if ($this->resourceManager->deleteResource($resource) === FALSE) {
				$this->outputLine('Could not remove ' . $resource->getUri());
			}
		}
	}

	/**
	 * Check if resource files are available
	 *
	 * @param array $assets
	 * @return bool
	 */
	protected function resourceFilesAvailable($assets) {
		$resourceFilesAvailable = TRUE;

		foreach($assets as $asset) {
			$originalResourcePath = 'resource://' . $asset->getResource()->getResourcePointer()->getHash();
			if (!file_exists($originalResourcePath)) {
				$this->outputLine('Missing resource file: ' . $originalResourcePath);
				$resourceFilesAvailable = FALSE;
			}
		}

		return $resourceFilesAvailable;
	}
}