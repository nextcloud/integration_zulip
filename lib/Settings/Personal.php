<?php

declare(strict_types=1);

namespace OCA\Zulip\Settings;

use OCA\Zulip\AppInfo\Application;
use OCA\Zulip\Service\SecretService;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Services\IInitialState;
use OCP\IConfig;
use OCP\Settings\ISettings;

class Personal implements ISettings {

	public function __construct(
		private IConfig $config,
		private IInitialState $initialStateService,
		private SecretService $secretService,
		private ?string $userId
	) {
	}

	/**
	 * @return TemplateResponse
	 */
	public function getForm(): TemplateResponse {
		$url = $this->config->getUserValue($this->userId, Application::APP_ID, 'url');
		$email = $this->config->getUserValue($this->userId, Application::APP_ID, 'email');
		$apiKey = $this->secretService->getEncryptedUserValue($this->userId, 'api_key');
		$fileActionEnabled = $this->config->getUserValue($this->userId, Application::APP_ID, 'file_action_enabled', '1') === '1';

		$userConfig = [
			'url' => $url,
			'email' => $email,
			'api_key' => $apiKey,
			'file_action_enabled' => $fileActionEnabled,
		];
		$this->initialStateService->provideInitialState('user-config', $userConfig);
		return new TemplateResponse(Application::APP_ID, 'personalSettings');
	}

	public function getSection(): string {
		return 'connected-accounts';
	}

	public function getPriority(): int {
		return 10;
	}
}
