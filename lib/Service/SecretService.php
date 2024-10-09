<?php

declare(strict_types=1);

namespace OCA\Zulip\Service;

use Exception;
use OCA\Zulip\AppInfo\Application;
use OCP\IConfig;
use OCP\IUserManager;
use OCP\PreConditionNotMetException;
use OCP\Security\ICrypto;

class SecretService {

	public function __construct(
		private IConfig $config,
		private IUserManager $userManager,
		private ICrypto $crypto,
	) {
	}

	/**
	 * @param string $userId
	 * @param string $key
	 * @param string $value
	 * @return void
	 * @throws PreConditionNotMetException
	 */
	public function setEncryptedUserValue(string $userId, string $key, string $value): void {
		if ($value === '') {
			$this->config->setUserValue($userId, Application::APP_ID, $key, '');
			return;
		}
		$encryptedValue = $this->crypto->encrypt($value);
		$this->config->setUserValue($userId, Application::APP_ID, $key, $encryptedValue);
	}

	/**
	 * @param string $userId
	 * @param string $key
	 * @return string
	 * @throws Exception
	 */
	public function getEncryptedUserValue(string $userId, string $key): string {
		$storedValue = $this->config->getUserValue($userId, Application::APP_ID, $key);
		if ($storedValue === '') {
			return '';
		}
		return $this->crypto->decrypt($storedValue);
	}
}
