<?php
namespace Lemming\AssetProcessor\Aop;

use TYPO3\Flow\Annotations as Flow;

/**
 * @Flow\Aspect
 */
class UploadAssetAspect {

	/**
	 * @Flow\Inject
	 * @var \Lemming\AssetProcessor\Service\AssetProcessService
	 */
	protected $assetProcessService;

	/**
	 * Process uploaded file
	 *
	 * @param  \TYPO3\Flow\Aop\JoinPointInterface $joinPoint: The current join point
	 * @return void
	 * @Flow\Before("method(TYPO3\Flow\Resource\ResourceManager->importUploadedResource()) && setting(Lemming.AssetProcessor.ProcessOnUpload)")
	 */
	public function processUploadedFile(\TYPO3\Flow\Aop\JoinPointInterface $joinPoint) {
		$arguments = $joinPoint->getMethodArguments();
		if (isset($arguments['uploadInfo']['name']) && isset($arguments['uploadInfo']['tmp_name'])) {
			$mediaType = \TYPO3\Flow\Utility\MediaTypes::getMediaTypeFromFilename(strtolower($arguments['uploadInfo']['name']));
			$this->assetProcessService->processFileByMediaType($arguments['uploadInfo']['tmp_name'], $mediaType);
		}
	}
}