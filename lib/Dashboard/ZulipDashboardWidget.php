<?php

declare(strict_types=1);

namespace OCA\Zulip\Dashboard;

use OCA\Zulip\AppInfo\Application;
use OCA\Zulip\Service\SecretService;
use OCP\AppFramework\Services\IInitialState;
use OCP\Dashboard\IWidget;
use OCP\IConfig;
use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\Util;

class ZulipDashboardWidget implements IWidget {

	public function __construct(
		private IL10N $l10n,
		private IConfig $config,
		private IURLGenerator $urlGenerator,
		private IInitialState $initialState,
		private SecretService $secretService,
		private ?string $userId,
	) {
	}

	public function getId(): string {
		return 'zulip_feed';
	}

	public function getTitle(): string {
		return $this->l10n->t('Zulip');
	}

	public function getOrder(): int {
		return 10;
	}

	public function getIconClass(): string {
		return 'icon-zulip';
	}

	public function getUrl(): ?string {
		return $this->urlGenerator->linkToRouteAbsolute('settings.PersonalSettings.index', ['section' => 'connected-accounts']);
	}

	public function load(): void {
		$url = $this->config->getUserValue($this->userId, Application::APP_ID, 'url', '');
		$email = $this->config->getUserValue($this->userId, Application::APP_ID, 'email', '');
		$apiKey = $this->secretService->getEncryptedUserValue($this->userId, 'api_key');

		$isConnected = ($url !== '' && $email !== '' && $apiKey !== '');

		$this->initialState->provideInitialState('dashboard-config', [
			'url' => $url,
			'is_connected' => $isConnected,
		]);

		Util::addScript(Application::APP_ID, Application::APP_ID . '-dashboard');
		Util::addStyle(Application::APP_ID, 'dashboard');
	}
}
