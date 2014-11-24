<?php
namespace Lemming\AssetProcessor\Service;

use TYPO3\Flow\Annotations as Flow;

/**
 * @Flow\Scope("singleton")
 */
class AssetProcessService {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Log\SystemLoggerInterface
	 */
	protected $logger;

	/**
	 * @var array
	 */
	protected $settings;

	/**
	 * @param array $settings
	 * @return void
	 */
	public function injectSettings(array $settings) {
		$this->settings = $settings;
	}

	/**
	 * Process a file
	 *
	 * @param $file
	 * @param $mediaType
	 */
	public function processFileByMediaType($file, $mediaType) {
		if (isset($this->settings['MediaTypes'][$mediaType]) && count($this->settings['MediaTypes'][$mediaType]) > 0) {
			foreach($this->settings['MediaTypes'][$mediaType] as $command) {
				$command = str_replace('{}', escapeshellarg($file), $command);
				$output = shell_exec($command . ' 2>&1');
				$this->logger->log('Executed: ' . $command, LOG_INFO);
				if(!empty($output)) {
					$this->logger->log('Output was: ' . $output, LOG_INFO);
				}
			}
		}
	}
}