import { generateUrl } from '@nextcloud/router'
import FileOutlineIcon from 'vue-material-design-icons/FileOutline.vue'
import OpenInNewIcon from 'vue-material-design-icons/OpenInNew.vue'
import LinkVariantIcon from 'vue-material-design-icons/LinkVariant.vue'

let mytimer = 0
export function delay(callback, ms) {
	return function() {
		const context = this
		const args = arguments
		clearTimeout(mytimer)
		mytimer = setTimeout(function() {
			callback.apply(context, args)
		}, ms || 0)
	}
}

export function gotoSettingsConfirmDialog() {
	const settingsLink = generateUrl('/settings/user/connected-accounts')
	OC.dialogs.message(
		t('integration_zulip', 'You need to connect a Zulip app before using the Zulip integration.')
		+ '<br><br>'
		+ t('integration_zulip', 'Do you want to go to your "Connect accounts" personal settings?'),
		t('integration_zulip', 'Connect to Zulip'),
		'none',
		{
			type: OC.dialogs.YES_NO_BUTTONS,
			confirm: t('integration_zulip', 'Go to settings'),
			confirmClasses: 'success',
			cancel: t('integration_zulip', 'Cancel'),
		},
		(result) => {
			if (result) {
				window.location.replace(settingsLink)
			}
		},
		true,
		true,
	)
}

export function humanFileSize(bytes, approx = false, si = false, dp = 1) {
	const thresh = si ? 1000 : 1024

	if (Math.abs(bytes) < thresh) {
		return bytes + ' B'
	}

	const units = si
		? ['kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB']
		: ['KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB']
	let u = -1
	const r = 10 ** dp

	do {
		bytes /= thresh
		++u
	} while (Math.round(Math.abs(bytes) * r) / r >= thresh && u < units.length - 1)

	if (approx) {
		return Math.floor(bytes) + ' ' + units[u]
	} else {
		return bytes.toFixed(dp) + ' ' + units[u]
	}
}

export const SEND_TYPE = {
	file: {
		id: 'file',
		label: t('integration_zulip', 'Upload files'),
		icon: FileOutlineIcon,
	},
	public_link: {
		id: 'public_link',
		label: t('integration_zulip', 'Public links'),
		icon: LinkVariantIcon,
	},
	internal_link: {
		id: 'internal_link',
		label: t('integration_zulip', 'Internal links (Only works for users with access to the files)'),
		icon: OpenInNewIcon,
	},
}
