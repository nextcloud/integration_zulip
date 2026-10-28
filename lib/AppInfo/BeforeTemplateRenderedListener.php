<?php

declare(strict_types=1);

namespace OCA\Zulip\AppInfo;

use OCP\AppFramework\Http\Events\BeforeTemplateRenderedEvent;
use OCP\AppFramework\Services\IInitialState;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\IConfig;

/** @template-implements IEventListener<BeforeTemplateRenderedEvent|Event> */
class BeforeTemplateRenderedListener implements IEventListener {

	public function __construct(
		private IConfig $config,
		private IInitialState $initialStateService,
	) {
	}

	public function handle(Event $event): void {
		if (!($event instanceof BeforeTemplateRenderedEvent)) {
			return;
		}
		if (!$event->isLoggedIn()) {
			return;
		}
		\OCP\Util::addStyle(Application::APP_ID, 'zulip-search');

		$adminConfig = [
			'send_type_enabled_file' => $this->config->getAppValue(Application::APP_ID, 'send_type_enabled_file', '1') === '1',
			'send_type_enabled_public_link' => $this->config->getAppValue(Application::APP_ID, 'send_type_enabled_public_link', '1') === '1',
			'send_type_enabled_internal_link' => $this->config->getAppValue(Application::APP_ID, 'send_type_enabled_internal_link', '1') === '1',
			'send_type_default' => $this->config->getAppValue(Application::APP_ID, 'send_type_default', ''),
		];
		$this->initialStateService->provideInitialState('admin-config', $adminConfig);
	}
}
