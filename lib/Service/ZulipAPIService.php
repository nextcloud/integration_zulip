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

namespace OCA\Zulip\Service;

use DateTime;
use Exception;
use OC\User\NoUserException;
use OCA\Zulip\AppInfo\Application;
use OCP\Constants;
use OCP\Files\File;
use OCP\Files\Folder;
use OCP\Files\IRootFolder;
use OCP\Files\NotPermittedException;
use OCP\Http\Client\IClient;
use OCP\Http\Client\IClientService;
use OCP\IConfig;
use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\PreConditionNotMetException;
use OCP\Security\ICrypto;
use OCP\Share\IManager as ShareManager;
use OCP\Share\IShare;
use Psr\Log\LoggerInterface;

/**
 * Service to make requests to Zulip API
 */
class ZulipAPIService {

	private IClient $client;

	public function __construct(
		private LoggerInterface $logger,
		private IL10N $l10n,
		private IConfig $config,
		private IRootFolder $root,
		private ShareManager $shareManager,
		private IURLGenerator $urlGenerator,
		private ICrypto $crypto,
		private NetworkService $networkService,
		IClientService $clientService
	) {
		$this->client = $clientService->newClient();
	}

	/**
	 * @param string $userId
	 * @param int $zulipUserId
	 * @return array
	 * @throws PreConditionNotMetException
	 */
	public function getUserAvatar(string $userId, int $zulipUserId): array {
		$userInfo = $this->request($userId, 'users/' . $zulipUserId, [
			'client_gravatar' => 'true',
		]);

		if (isset($userInfo['error'])) {
			return ['displayName' => 'User'];
		}

		if (is_null($userInfo['user']['avatar_url'])) {
			return ['displayName' => $userInfo['user']['full_name']];
		}

		$image = $this->networkService->requestAvatar($userId, $userInfo['user']['avatar_url']);

		if (!is_array($image)) {
			return ['avatarContent' => $image];
		}

		return ['displayName' => $userInfo['user']['full_name']];
	}

	/**
	 * @param string $userId
	 * @return array
	 * @throws PreConditionNotMetException
	 */
	public function getMyChannels(string $userId): array {
		$zulipUrl = $this->config->getUserValue($userId, Application::APP_ID, 'url');

		$channelResult = $this->request($userId, 'streams', [
			'include_web_public' => 'true',
		]);

		if (isset($channelResult['error'])) {
			return (array) $channelResult;
		}

		if (!isset($channelResult['streams']) || !is_array($channelResult['streams'])) {
			return ['error' => 'No channels found'];
		}

		$userResult = $this->request($userId, 'messages', [
			'anchor' => 'newest',
			'num_before' => 5000,
			'num_after' => 0,
			'narrow' => '[{"operator": "is", "operand": "dm"}]',
			'client_gravatar' => 'true',
		]);

		if (isset($userResult['error'])) {
			return (array) $userResult;
		}

		$conversations = [];

		foreach($channelResult['streams'] as $channel) {
			$conversations[] = [
				'type' => 'channel',
				'channel_id' => $channel['stream_id'],
				'name' => $channel['name'],
				'invite_only' => $channel['invite_only'],
				'is_web_public' => $channel['is_web_public'],
			];
		}

		$users = [];

		foreach($userResult['messages'] as $user) {
			$users[$user['sender_id']] = [
				'type' => 'direct',
				'user_id' => $user['sender_id'],
				'name' => $user['sender_full_name'],
				'avatar_url' => $user['avatar_url'],
			];
		}

		return array_merge($conversations, $users);
	}

	/**
	 * @param string $userId
	 * @param int $channelId
	 * @return array
	 * @throws PreConditionNotMetException
	 */
	public function getMyTopics(string $userId, int $channelId): array {
		$topicResult = $this->request($userId, 'users/me/' . $channelId . '/topics');

		if (isset($topicResult['error'])) {
			return (array) $topicResult;
		}

		if (!isset($topicResult['topics']) || !is_array($topicResult['topics'])) {
			return ['error' => 'No topics found'];
		}

		return $topicResult['topics'];
	}

	/**
	 * @param string $userId
	 * @param string $messageType
	 * @param string $message
	 * @param int $channelId
	 * @param string|null $topicName
	 * @return array|string[]
	 * @throws PreConditionNotMetException
	 */
	public function sendMessage(string $userId, string $messageType, string $message,
		int $channelId, ?string $topicName = null): array {
		$params = [
			'type' => $messageType,
			'to' => $messageType === 'channel' ? $channelId : '[' . $channelId . ']',
			'topic' => $topicName,
			'content' => $message,
		];
		return $this->request($userId, 'messages', $params, 'POST');
	}

	/**
	 * @param string $userId
	 * @param array $fileIds
	 * @param string $messageType
	 * @param int $channelId
	 * @param string $channelName
	 * @param string $topicName
	 * @param string $comment
	 * @param string $permission
	 * @param string|null $expirationDate
	 * @param string|null $password
	 * @return array|string[]
	 * @throws NoUserException
	 * @throws NotPermittedException
	 * @throws PreConditionNotMetException
	 */
	public function sendPublicLinks(string $userId, array $fileIds, string $messageType,
		int $channelId, string $channelName, string $topicName, string $comment,
		string $permission, ?string $expirationDate = null, ?string $password = null): array {
		$links = [];
		$userFolder = $this->root->getUserFolder($userId);

		// create public links
		foreach ($fileIds as $fileId) {
			$nodes = $userFolder->getById($fileId);
			// if (count($nodes) > 0 && $nodes[0] instanceof File) {
			if (count($nodes) > 0 && ($nodes[0] instanceof File || $nodes[0] instanceof Folder)) {
				$node = $nodes[0];

				$share = $this->shareManager->newShare();
				$share->setNode($node);

				if ($permission === 'edit') {
					$share->setPermissions(Constants::PERMISSION_READ | Constants::PERMISSION_UPDATE);
				} else {
					$share->setPermissions(Constants::PERMISSION_READ);
				}

				$share->setShareType(IShare::TYPE_LINK);
				$share->setSharedBy($userId);
				$share->setLabel('Zulip (' . $channelName . '/' . $topicName . ')');

				if ($expirationDate !== null) {
					$share->setExpirationDate(new DateTime($expirationDate));
				}

				if ($password !== null) {
					try {
						$share->setPassword($password);
					} catch (Exception $e) {
						return ['error' => $e->getMessage()];
					}
				}

				try {
					$share = $this->shareManager->createShare($share);
					if ($expirationDate === null) {
						$share->setExpirationDate(null);
						$this->shareManager->updateShare($share);
					}
				} catch (Exception $e) {
					return ['error' => $e->getMessage()];
				}

				$token = $share->getToken();
				$linkUrl = $this->urlGenerator->getAbsoluteURL(
					$this->urlGenerator->linkToRoute('files_sharing.Share.showShare', [
						'token' => $token,
					])
				);

				$links[] = [
					'name' => $node->getName(),
					'url' => $linkUrl,
				];
			}
		}

		if (count($links) === 0) {
			return ['error' => 'Files not found'];
		}

		$message = ($comment !== ''
			? $comment . "\n\n"
			: '') . join("\n", array_map(fn ($link) => '[' . $link['name'] . '](' . $link['url'] . ')', $links));

		return $this->sendMessage($userId, $messageType, $message, $channelId, $topicName);
	}

	/**
	 * @param string $userId
	 * @param int $fileId
	 * @param string $messageType
	 * @param int $channelId
	 * @param string $comment
	 * @param string|null $topicName
	 * @return array|string[]
	 * @throws NoUserException
	 * @throws NotPermittedException
	 */
	public function sendFile(string $userId, int $fileId, string $messageType,
		int $channelId, string $comment = '', ?string $topicName = null): array {
		$zulipUrl = $this->config->getUserValue($userId, Application::APP_ID, 'url');
		$userFolder = $this->root->getUserFolder($userId);
		$files = $userFolder->getById($fileId);

		if (count($files) > 0 && $files[0] instanceof File) {
			$file = $files[0];

			$sendResult = $this->networkService->requestSendFile($userId, 'user_uploads', $file);

			if (isset($sendResult['error'])) {
				return (array) $sendResult;
			}

			$fileLink = rtrim($zulipUrl, '/') . $sendResult['uri'];
			$message = ($comment !== '' ? $comment . "\n\n" : '')
				. '[' . $file->getName() . '](' . $fileLink . ')';

			$messageResult = $this->sendMessage($userId, $messageType, $message, $channelId, $topicName);

			if (isset($messageResult['error'])) {
				return $messageResult;
			}

			return ['success' => true];
		} else {
			return ['error' => 'File not found'];
		}
	}

	/**
	 * @param string $userId
	 * @param string $endPoint
	 * @param array $params
	 * @param string $method
	 * @param bool $jsonResponse
	 * @param bool $zulipApiRequest
	 * @return array|mixed|resource|string|string[]
	 * @throws PreConditionNotMetException
	 */
	public function request(string $userId, string $endPoint, array $params = [], string $method = 'GET',
		bool $jsonResponse = true, bool $zulipApiRequest = true) {
		return $this->networkService->request($userId, $endPoint, $params, $method, $jsonResponse, $zulipApiRequest);
	}
}
