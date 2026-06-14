<?php

declare(strict_types=1);

namespace OCA\Zulip\Dashboard;

use OCA\Zulip\AppInfo\Application;
use OCA\Zulip\Service\SecretService;
use OCA\Zulip\Service\ZulipAPIService;
use OCP\Dashboard\IButtonWidget;
use OCP\Dashboard\IIconWidget;
use OCP\Dashboard\IReloadableWidget;
use OCP\Dashboard\Model\WidgetButton;
use OCP\Dashboard\Model\WidgetItem;
use OCP\Dashboard\Model\WidgetItems;
use OCP\IConfig;
use OCP\IL10N;
use OCP\IURLGenerator;

class ZulipDashboardWidget implements IReloadableWidget, IButtonWidget, IIconWidget {

	public function __construct(
		private IL10N $l10n,
		private IConfig $config,
		private IURLGenerator $urlGenerator,
		private SecretService $secretService,
		private ZulipAPIService $zulipAPIService,
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
		return '';
	}

	public function getIconUrl(): string {
		return $this->urlGenerator->getAbsoluteURL(
			$this->urlGenerator->imagePath(Application::APP_ID, 'app-dark.svg')
		);
	}

	public function getUrl(): ?string {
		$zulipUrl = rtrim($this->config->getUserValue($this->userId ?? '', Application::APP_ID, 'url', ''), '/');
		if ($zulipUrl !== '') {
			return $zulipUrl . '/#feed';
		}
		return $this->urlGenerator->linkToRouteAbsolute('settings.PersonalSettings.index', ['section' => 'connected-accounts']);
	}

	public function load(): void {
	}

	public function getItemsV2(string $userId, ?string $since = null, int $limit = 7): WidgetItems {
		$url = $this->config->getUserValue($userId, Application::APP_ID, 'url', '');
		$email = $this->config->getUserValue($userId, Application::APP_ID, 'email', '');
		$apiKey = $this->secretService->getEncryptedUserValue($userId, 'api_key');
		$isConnected = ($url !== '' && $email !== '' && $apiKey !== '');

		if (!$isConnected) {
			return new WidgetItems([], $this->l10n->t('Connect your Zulip account in the settings'));
		}

		$showUnread = $this->config->getUserValue($userId, Application::APP_ID, 'dashboard_show_unread', '0') === '1';

		if ($showUnread) {
			$messages = $this->zulipAPIService->getUnreadMessages($userId, $limit);
			$emptyMessage = $this->l10n->t('No unread messages');
		} else {
			$messages = $this->zulipAPIService->getRecentMessages($userId, $limit);
			$emptyMessage = $this->l10n->t('No recent messages');
		}

		if (isset($messages['error'])) {
			return new WidgetItems([], $this->l10n->t('Failed to load messages'));
		}

		$zulipUrl = rtrim($this->config->getUserValue($userId, Application::APP_ID, 'url', ''), '/');

		$items = array_map(function (array $msg) use ($zulipUrl): WidgetItem {
			return new WidgetItem(
				$msg['content'] ?? '',
				$this->_buildSubtitle($msg),
				$this->_buildMessageUrl($msg, $zulipUrl),
				$this->urlGenerator->linkToRouteAbsolute(
					'integration_zulip.ZulipAPI.getUserAvatar',
					['zulipUserId' => $msg['sender_id'] ?? 0]
				),
				(string)($msg['id'] ?? ''),
			);
		}, $messages);

		return new WidgetItems($items, $emptyMessage);
	}

	public function getReloadInterval(): int {
		return 60;
	}

	public function getWidgetButtons(string $userId): array {
		$zulipUrl = rtrim($this->config->getUserValue($userId, Application::APP_ID, 'url', ''), '/');
		if ($zulipUrl === '') {
			return [];
		}
		$showUnread = $this->config->getUserValue($userId, Application::APP_ID, 'dashboard_show_unread', '0') === '1';
		if ($showUnread) {
			return [
				new WidgetButton(
					WidgetButton::TYPE_MORE,
					$zulipUrl . '/#inbox',
					$this->l10n->t('Open Zulip inbox'),
				),
			];
		}
		return [
			new WidgetButton(
				WidgetButton::TYPE_MORE,
				$zulipUrl . '/#feed',
				$this->l10n->t('Open Zulip feed'),
			),
		];
	}

	private function _buildSubtitle(array $msg): string {
		if (($msg['type'] ?? '') === 'stream') {
			$subject = $msg['subject'] ?? '';
			$isDefaultTopic = $subject === '' || strtolower($subject) === 'general chat';
			return '#' . ($msg['display_recipient'] ?? '') . ($isDefaultTopic ? '' : ' › ' . $subject);
		}
		return $this->l10n->t('Direct message from {name}', ['name' => $msg['sender_full_name'] ?? '']);
	}

	private function _buildMessageUrl(array $msg, string $zulipUrl): string {
		if ($zulipUrl === '') {
			return '';
		}
		if (($msg['type'] ?? '') === 'private') {
			$recipients = is_array($msg['display_recipient'] ?? null) ? $msg['display_recipient'] : [];
			$userIds = implode(',', array_column($recipients, 'id'));
			return $zulipUrl . '/#narrow/dm/' . $userIds . '/near/' . ($msg['id'] ?? '');
		}
		$subject = $msg['subject'] ?? '';
		$isDefaultTopic = $subject === '' || strtolower($subject) === 'general chat';
		$base = $zulipUrl . '/#narrow/channel/' . ($msg['stream_id'] ?? '');
		if ($isDefaultTopic) {
			return $base . '/near/' . ($msg['id'] ?? '');
		}
		return $base . '/topic/' . str_replace('%', '.', rawurlencode($subject)) . '/with/' . ($msg['id'] ?? '');
	}
}
