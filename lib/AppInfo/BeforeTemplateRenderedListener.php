<?php

declare(strict_types=1);

namespace OCA\Zulip\AppInfo;

use OCP\AppFramework\Http\Events\BeforeTemplateRenderedEvent;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;

/** @template-implements IEventListener<BeforeTemplateRenderedEvent|Event> */
class BeforeTemplateRenderedListener implements IEventListener {
	public function handle(Event $event): void {
		if (!($event instanceof BeforeTemplateRenderedEvent)) {
			return;
		}
		if (!$event->isLoggedIn()) {
			return;
		}
		\OCP\Util::addStyle(Application::APP_ID, 'zulip-search');
	}
}
