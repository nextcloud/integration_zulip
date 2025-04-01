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

use Exception;
use OC\User\NoUserException;
use OCA\Zulip\Service\ZulipAPIService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\FrontpageRoute;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\DataDisplayResponse;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\RedirectResponse;
use OCP\Files\NotPermittedException;
use OCP\IConfig;
use OCP\IRequest;
use OCP\IURLGenerator;
use OCP\Lock\LockedException;

class ZulipAPIController extends Controller {

	public function __construct(
		string $appName,
		IRequest $request,
		private IConfig $config,
		private IURLGenerator $urlGenerator,
		private ZulipAPIService $zulipAPIService,
		private ?string $userId,
	) {
		parent::__construct($appName, $request);
	}

	/**
	 * Get Zulip user avatar
	 *
	 * @param int $zulipUserId
	 * @param int $useFallback
	 * @return DataDisplayResponse|RedirectResponse
	 * @throws \Exception
	 */
	#[NoAdminRequired]
	#[NoCSRFRequired]
	#[FrontpageRoute(verb: 'GET', url: '/users/{zulipUserId}/image')]
	public function getUserAvatar(int $zulipUserId, int $useFallback = 1): DataDisplayResponse|RedirectResponse {
		$result = $this->zulipAPIService->getUserAvatar($this->userId, $zulipUserId);
		if (isset($result['avatarContent'])) {
			$response = new DataDisplayResponse($result['avatarContent']);
			$response->cacheFor(60 * 60 * 24);
			return $response;
		}
		if ($useFallback !== 0 && isset($result['displayName'])) {
			$fallbackAvatarUrl = $this->urlGenerator->linkToRouteAbsolute('core.GuestAvatar.getAvatar', ['guestName' => $result['displayName'], 'size' => 44]);
			return new RedirectResponse($fallbackAvatarUrl);
		}
		return new DataDisplayResponse('', Http::STATUS_NOT_FOUND);
	}

	/**
	 * @return DataResponse
	 * @throws Exception
	 */
	#[NoAdminRequired]
	#[FrontpageRoute(verb: 'GET', url: '/channels')]
	public function getChannels() {
		$result = $this->zulipAPIService->getMyChannels($this->userId);
		if (isset($result['error'])) {
			return new DataResponse($result, Http::STATUS_BAD_REQUEST);
		}
		return new DataResponse($result);
	}

	/**
	 * @param int $channelId
	 * @return DataResponse
	 * @throws Exception
	 */
	#[NoAdminRequired]
	#[FrontpageRoute(verb: 'GET', url: '/channels/{channelId}/topics')]
	public function getTopics(int $channelId) {
		$result = $this->zulipAPIService->getMyTopics($this->userId, $channelId);
		if (isset($result['error'])) {
			return new DataResponse($result, Http::STATUS_BAD_REQUEST);
		}
		return new DataResponse($result);
	}

	/**
	 * @param string $messageType
	 * @param string $message
	 * @param int $channelId
	 * @param string|null $topicName
	 * @return DataResponse
	 * @throws Exception
	 */
	#[NoAdminRequired]
	#[FrontpageRoute(verb: 'POST', url: '/sendMessage')]
	public function sendMessage(string $messageType, string $message, int $channelId,
		?string $topicName = null) {
		$result = $this->zulipAPIService->sendMessage($this->userId, $messageType, $message, $channelId, $topicName);
		if (isset($result['error'])) {
			return new DataResponse($result['error'], Http::STATUS_BAD_REQUEST);
		} else {
			return new DataResponse($result);
		}
	}

	/**
	 * @param int $fileId
	 * @param string $messageType
	 * @param int $channelId
	 * @param string $comment
	 * @param string|null $topicName
	 * @return DataResponse
	 * @throws LockedException
	 * @throws NoUserException
	 * @throws NotPermittedException
	 */
	#[NoAdminRequired]
	#[FrontpageRoute(verb: 'POST', url: '/sendFile')]
	public function sendFile(int $fileId, string $messageType, int $channelId,
		string $comment = '', ?string $topicName = null) {
		$result = $this->zulipAPIService->sendFile($this->userId, $fileId, $messageType, $channelId, $comment, $topicName);
		if (isset($result['error'])) {
			return new DataResponse($result['error'], Http::STATUS_BAD_REQUEST);
		} else {
			return new DataResponse($result);
		}
	}

	/**
	 * @param array $fileIds
	 * @param string $messageType
	 * @param int $channelId
	 * @param string $channelName
	 * @param string $topicName
	 * @param string $comment
	 * @param string $permission
	 * @param string|null $expirationDate
	 * @param string|null $password
	 * @return DataResponse
	 * @throws NoUserException
	 * @throws NotPermittedException
	 */
	#[NoAdminRequired]
	#[FrontpageRoute(verb: 'POST', url: '/sendPublicLinks')]
	public function sendPublicLinks(array $fileIds, string $messageType, int $channelId,
		string $channelName, string $topicName, string $comment,
		string $permission, ?string $expirationDate = null, ?string $password = null): DataResponse {
		$result = $this->zulipAPIService->sendPublicLinks(
			$this->userId, $fileIds, $messageType, $channelId, $channelName, $topicName,
			$comment, $permission, $expirationDate, $password
		);
		if (isset($result['error'])) {
			return new DataResponse($result['error'], Http::STATUS_BAD_REQUEST);
		} else {
			return new DataResponse($result);
		}
	}
}
