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

namespace OCA\Zulip\Controller;

use OCA\Zulip\AppInfo\Application;
use OCA\Zulip\Service\SecretService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\FrontpageRoute;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\PasswordConfirmationRequired;
use OCP\AppFramework\Http\DataResponse;
use OCP\IConfig;
use OCP\IRequest;
use OCP\PreConditionNotMetException;

class ConfigController extends Controller {

	public function __construct(
		string $appName,
		IRequest $request,
		private IConfig $config,
		private SecretService $secretService,
		private ?string $userId,
	) {
		parent::__construct($appName, $request);
	}

	/**
	 * @return DataResponse
	 */
	#[NoAdminRequired]
	#[FrontpageRoute(verb: 'GET', url: '/is-connected')]
	public function isUserConnected(): DataResponse {
		$url = $this->config->getUserValue($this->userId, Application::APP_ID, 'url');
		$email = $this->config->getUserValue($this->userId, Application::APP_ID, 'email');
		$apiKey = $this->secretService->getEncryptedUserValue($this->userId, 'api_key');

		return new DataResponse([
			'connected' => ($url !== '' && $email !== '' && $apiKey !== ''),
		]);
	}

	/**
	 * set config values
	 *
	 * @param array $values
	 * @return DataResponse
	 * @throws PreConditionNotMetException
	 */
	#[NoAdminRequired]
	#[FrontpageRoute(verb: 'PUT', url: '/config')]
	public function setConfig(array $values): DataResponse {
		foreach ($values as $key => $value) {
			if ($key === 'api_key') {
				return new DataResponse([], Http::STATUS_BAD_REQUEST);
			}

			$this->config->setUserValue($this->userId, Application::APP_ID, $key, $value);
		}

		return new DataResponse([]);
	}

	/**
	 * set sensitive config values
	 *
	 * @param array $values
	 * @return DataResponse
	 * @throws PreConditionNotMetException
	 */
	#[NoAdminRequired]
	#[PasswordConfirmationRequired]
	#[FrontpageRoute(verb: 'PUT', url: '/sensitive-config')]
	public function setSensitiveConfig(array $values): DataResponse {
		foreach ($values as $key => $value) {
			if ($key === 'api_key') {
				$this->secretService->setEncryptedUserValue($this->userId, $key, $value);
			} else {
				$this->config->setUserValue($this->userId, Application::APP_ID, $key, $value);
			}
		}

		return new DataResponse([]);
	}
}
