<?php
/**
 * Nextcloud - Zulip
 *
 *
 * @author Julien Veyssier <julien-nc@posteo.net>
 * @author Anupam Kumar <kyteinsky@gmail.com>
 * @copyright Julien Veyssier 2022
 * @copyright Anupam Kumar 2023
 */

namespace OCA\Zulip\AppInfo;

use OCA\Zulip\Listener\FilesMenuListener;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\Collaboration\Resources\LoadAdditionalScriptsEvent;

class Application extends App implements IBootstrap {
	public const APP_ID = 'integration_zulip';
	public const INTEGRATION_USER_AGENT = 'Nextcloud Zulip Integration';
	public const ZULIP_API_URL = 'https://zulip.com/api/';
	public const ZULIP_OAUTH_ACCESS_URL = 'https://zulip.com/api/oauth.v2.access';

	public function __construct(array $urlParams = []) {
		parent::__construct(self::APP_ID, $urlParams);
	}

	public function register(IRegistrationContext $context): void {
		$context->registerEventListener(LoadAdditionalScriptsEvent::class, FilesMenuListener::class);
	}

	public function boot(IBootContext $context): void {
	}
}
