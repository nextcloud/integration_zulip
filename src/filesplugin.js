/*
 * Copyright (c) 2022 Julien Veyssier <julien-nc@posteo.net>
 * Copyright (c) 2024 Edward Ly <contact@edward.ly>
 *
 * This file is licensed under the Affero General Public License version 3
 * or later.
 *
 * See the COPYING-README file.
 *
 */
import GoToSettingsDialog from './components/GoToSettingsDialog.vue'
import SendFilesModal from './components/SendFilesModal.vue'

import { createApp } from 'vue'

import axios from '@nextcloud/axios'
import moment from '@nextcloud/moment'
import { generateUrl, linkTo } from '@nextcloud/router'
import { showSuccess, showError } from '@nextcloud/dialogs'
import { translate as t, translatePlural as n } from '@nextcloud/l10n'
import { SEND_TYPE } from './utils.js'
import {
	registerFileAction, Permission, FileType,
} from '@nextcloud/files'
import { subscribe } from '@nextcloud/event-bus'
import ZulipIcon from '../img/app-dark.svg'

import { getCSPNonce } from '@nextcloud/auth'

__webpack_nonce__ = getCSPNonce() // eslint-disable-line
__webpack_public_path__ = linkTo('integration_zulip', 'js/') // eslint-disable-line

const DEBUG = false

const SEND_MESSAGE_URL = generateUrl('/apps/integration_zulip/sendMessage')
const SEND_FILE_URL = generateUrl('/apps/integration_zulip/sendFile')
const SEND_PUBLIC_LINKS_URL = generateUrl('/apps/integration_zulip/sendPublicLinks')
const IS_CONNECTED_URL = generateUrl('/apps/integration_zulip/is-connected')

if (!OCA.Zulip) {
	OCA.Zulip = {
		actionIgnoreLists: [
			'trashbin',
			'files.public',
		],
		filesToSend: [],
		currentFileList: null,
	}
}

subscribe('files:list:updated', onFilesListUpdated)
function onFilesListUpdated({ view, folder, contents }) {
	OCA.Zulip.currentFileList = { view, folder, contents }
}

function openChannelSelector(files) {
	OCA.Zulip.filesToSend = files
	const modalVue = OCA.Zulip.ZulipSendModalVue
	modalVue.updateChannels()
	modalVue.setFiles([...files])
	modalVue.showModal()
}

const sendAction = {
	id: 'zulipSend',
	displayName: ({ nodes }) => {
		return nodes.length > 1
			? t('integration_zulip', 'Send files to Zulip')
			: t('integration_zulip', 'Send file to Zulip')
	},
	enabled({ nodes, view }) {
		return !OCA.Zulip.actionIgnoreLists.includes(view.id)
			&& nodes.length > 0
			&& !nodes.some(({ permissions }) => (permissions & Permission.READ) === 0)
		// && nodes.every(({ type }) => type === FileType.File)
		// && nodes.every(({ mime }) => mime === 'application/some+type')
	},
	iconSvgInline: () => ZulipIcon,
	async exec({ nodes }) {
		sendSelectedNodes([nodes[0]])
		return null
	},
	async execBatch({ nodes }) {
		sendSelectedNodes(nodes)
		return nodes.map(_ => null)
	},
}
registerFileAction(sendAction)

function sendSelectedNodes(nodes) {
	const formattedNodes = nodes.map((node) => {
		return {
			id: node.fileid,
			name: node.basename,
			type: node.type,
			size: node.size,
		}
	})
	if (OCA.Zulip.zulipConnected) {
		openChannelSelector(formattedNodes)
	} else {
		OCA.Zulip.ZulipSettingsDialogVue.showDialog()
	}
}

async function sendPublicLinks(messageType, channelId, channelName, topicName, comment, permission, expirationDate, password) {
	const req = {
		fileIds: OCA.Zulip.filesToSend.map((f) => f.id),
		messageType,
		channelId,
		channelName,
		topicName,
		comment,
		permission,
		expirationDate: expirationDate ? moment(expirationDate).format('YYYY-MM-DD') : undefined,
		password,
	}

	return axios.post(SEND_PUBLIC_LINKS_URL, req)
}

const sendInternalLinks = async (messageType, comment, channelId, topicName) => {
	const getLink = (file) => window.location.protocol + '//' + window.location.host + generateUrl('/f/' + file.id)
	const message = (comment !== ''
		? `${comment}\n\n`
		: '') + `${OCA.Zulip.filesToSend.map((file) => `[${file.name}](${getLink(file)})`).join('\n')}`
	return sendMessage(messageType, message, channelId, topicName)
}

const sendFile
	= (messageType, channelId, channelName, topicName, comment) => (file, i) => new Promise((resolve, reject) => {
		OCA.Zulip.ZulipSendModalVue.fileStarted(file.id)

		// send the comment only with the first file
		const req = {
			fileId: file.id,
			messageType,
			channelId,
			...(i === 0 && { comment }),
			topicName,
		}

		axios.post(SEND_FILE_URL, req).then((response) => {
			OCA.Zulip.remoteFileIds.push(response.data.remote_file_id)
			OCA.Zulip.sentFileNames.push(file.name)
			OCA.Zulip.ZulipSendModalVue.fileFinished(file.id)

			resolve()
		}).catch((error) => {
			showError(
				t('integration_zulip', 'Failed to send {name} to {channelName} on Zulip',
					{ name: file.name, channelName })
				+ ': ' + error.response?.request?.responseText,
			)
			reject(error)
		})
	})

async function sendMessage(messageType, message, channelId, topicName) {
	const req = {
		messageType,
		message,
		channelId,
		topicName,
	}
	return axios.post(SEND_MESSAGE_URL, req)
}

// settings dialog
const dialogId = 'zulipSettingsDialog'
const dialogElement = document.createElement('div')
dialogElement.id = dialogId
document.body.append(dialogElement)

const dialogApp = createApp(GoToSettingsDialog)
dialogApp.mixin({ methods: { t, n } })
OCA.Zulip.ZulipSettingsDialogVue = dialogApp.mount(dialogElement)

dialogElement.addEventListener('closing', () => {
	if (DEBUG) console.debug('[Zulip] settings dialog closed')
})

// send file modal
const modalId = 'zulipSendModal'
const modalElement = document.createElement('div')
modalElement.id = modalId
document.body.append(modalElement)

const modalApp = createApp(SendFilesModal)
modalApp.mixin({ methods: { t, n } })
OCA.Zulip.ZulipSendModalVue = modalApp.mount(modalElement)

modalElement.addEventListener('closed', () => {
	if (DEBUG) console.debug('[Zulip] send modal closed')
})
modalElement.addEventListener('validate', (data) => {
	const {
		filesToSend, messageType, channelId, channelName, topicName,
		type, comment, permission, expirationDate, password,
	} = data.detail

	if (filesToSend.length === 0) {
		return
	}

	OCA.Zulip.filesToSend = filesToSend

	if (type === SEND_TYPE.public_link.id) {
		sendPublicLinks(messageType, channelId, channelName, topicName, comment, permission, expirationDate, password).then(() => {
			showSuccess(
				n(
					'integration_zulip',
					'A link to {fileName} was sent to {channelName}',
					'All of the {number} links were sent to {channelName}',
					OCA.Zulip.filesToSend.length,
					{
						fileName: OCA.Zulip.filesToSend[0].name,
						channelName,
						number: OCA.Zulip.filesToSend.length,
					},
				),
			)
			OCA.Zulip.ZulipSendModalVue.success()
		}).catch((error) => {
			errorCallback(error)
			showError(
				t('integration_zulip', 'Failed to send links to Zulip')
				+ ' ' + error.response?.request?.responseText,
			)
		})
	} else if (type === SEND_TYPE.internal_link.id) {
		sendInternalLinks(messageType, comment, channelId, topicName).then(() => {
			showSuccess(
				n(
					'integration_zulip',
					'A link to {fileName} was sent to {channelName}',
					'All of the {number} links were sent to {channelName}',
					OCA.Zulip.filesToSend.length,
					{
						fileName: OCA.Zulip.filesToSend[0].name,
						number: OCA.Zulip.filesToSend.length,
						channelName,
					},
				),
			)
			OCA.Zulip.ZulipSendModalVue.success()
		}).catch((error) => {
			errorCallback(error)
			showError(
				n(
					'integration_zulip',
					'Failed to send the internal link to {channelName}',
					'Failed to send internal links to {channelName}',
					OCA.Zulip.filesToSend.length,
					{
						fileName: OCA.Zulip.filesToSend[0].name,
						channelName,
					},
				)
				+ ': ' + error.response?.request?.responseText,
			)
		})
	} else {
		OCA.Zulip.remoteFileIds = []
		OCA.Zulip.sentFileNames = []
		OCA.Zulip.filesToSend = filesToSend.filter((f) => f.type !== FileType.Folder)

		Promise.all(OCA.Zulip.filesToSend.map(sendFile(messageType, channelId, channelName, topicName, comment))).then(() => {
			showSuccess(
				n(
					'integration_zulip',
					'{fileName} was successfully sent to {channelName}',
					'All of the {number} files were sent to {channelName}',
					OCA.Zulip.filesToSend.length,
					{
						fileName: OCA.Zulip.filesToSend[0].name,
						number: OCA.Zulip.filesToSend.length,
						channelName,
					},
				),
			)
			OCA.Zulip.ZulipSendModalVue.success()
		}).catch(errorCallback)
	}
})

function errorCallback(error) {
	console.error(error)
	OCA.Zulip.ZulipSendModalVue.failure()
	OCA.Zulip.filesToSend = []
	OCA.Zulip.sentFileNames = []
}

// get Zulip state
axios.get(IS_CONNECTED_URL).then((response) => {
	OCA.Zulip.zulipConnected = response.data.connected
	if (DEBUG) console.debug('[Zulip] OCA.Zulip', OCA.Zulip)
}).catch((error) => {
	console.error(error)
})
