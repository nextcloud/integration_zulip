<?php

declare(strict_types=1);

namespace OCA\Zulip\Settings;

use OCA\Zulip\AppInfo\Application;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Services\IInitialState;
use OCP\IConfig;
use OCP\Settings\ISettings;

class Admin implements ISettings {

	public function __construct(
		private IConfig $config,
		private IInitialState $initialStateService,
	) {
	}

	public function getForm(): TemplateResponse {
		$adminConfig = [
			'send_type_enabled_file' => $this->config->getAppValue(Application::APP_ID, 'send_type_enabled_file', '1') === '1',
			'send_type_enabled_public_link' => $this->config->getAppValue(Application::APP_ID, 'send_type_enabled_public_link', '1') === '1',
			'send_type_enabled_internal_link' => $this->config->getAppValue(Application::APP_ID, 'send_type_enabled_internal_link', '1') === '1',
			'send_type_default' => $this->config->getAppValue(Application::APP_ID, 'send_type_default', ''),
		];
		$this->initialStateService->provideInitialState('admin-config', $adminConfig);
		return new TemplateResponse(Application::APP_ID, 'adminSettings');
	}

	public function getSection(): string {
		return 'connected-accounts';
	}

	public function getPriority(): int {
		return 10;
	}
}
