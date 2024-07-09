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

return [
	'routes' => [
		['name' => 'config#isUserConnected', 'url' => '/is-connected', 'verb' => 'GET'],
		['name' => 'config#setConfig', 'url' => '/config', 'verb' => 'PUT'],

		['name' => 'zulipAPI#sendMessage', 'url' => '/sendMessage', 'verb' => 'POST'],
		['name' => 'zulipAPI#sendPublicLinks', 'url' => '/sendPublicLinks', 'verb' => 'POST'],
		['name' => 'zulipAPI#sendFile', 'url' => '/sendFile', 'verb' => 'POST'],
		['name' => 'zulipAPI#getChannels', 'url' => '/channels', 'verb' => 'GET'],
		['name' => 'zulipAPI#getTopics', 'url' => '/channels/{channelId}/topics', 'verb' => 'GET'],
		['name' => 'zulipAPI#getUserAvatar', 'url' => '/users/{zulipUserId}/image', 'verb' => 'GET'],

		['name' => 'files#getFileImage', 'url' => '/preview', 'verb' => 'GET'],
	]
];
