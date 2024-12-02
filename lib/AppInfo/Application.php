<?php
/**
 * Nextcloud - Zulip
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Julien Veyssier <julien-nc@posteo.net>
 * @author Anupam Kumar <kyteinsky@gmail.com>
 * @author Edward Ly <contact@edward.ly>
 * @copyright Julien Veyssier 2022
 * @copyright Anupam Kumar 2023
 * @copyright Edward Ly 2024
 */

declare(strict_types=1);

namespace OCA\Zulip\AppInfo;

use OCA\Zulip\Listener\FilesMenuListener;
use OCA\Zulip\Search\ZulipSearchMessagesProvider;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\AppFramework\Http\Events\BeforeTemplateRenderedEvent;
use OCP\Collaboration\Resources\LoadAdditionalScriptsEvent;

class Application extends App implements IBootstrap {
	public const APP_ID = 'integration_zulip';
	public const INTEGRATION_USER_AGENT = 'Nextcloud Zulip Integration';

	public function __construct(array $urlParams = []) {
		parent::__construct(self::APP_ID, $urlParams);
	}

	public function register(IRegistrationContext $context): void {
		$context->registerEventListener(LoadAdditionalScriptsEvent::class, FilesMenuListener::class);
		$context->registerEventListener(BeforeTemplateRenderedEvent::class, BeforeTemplateRenderedListener::class);
		$context->registerSearchProvider(ZulipSearchMessagesProvider::class);
	}

	public function boot(IBootContext $context): void {
	}
}
