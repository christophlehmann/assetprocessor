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
				$command = str_replace('{}', escapeshellarg($file), $command) . ' 2>&1';
				exec($command, $output, $exitCode);
				if ($exitCode != 0) {
					$this->logger->log('Executed: ' . $command, LOG_ERR);
					foreach($output as $line) {
						$this->logger->log('Ouput was: ' . $line, LOG_ERR);
					}
				}
			}
		}
	}
}