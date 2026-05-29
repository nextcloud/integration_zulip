<template>
	<NcDashboardWidget
		:items="items"
		:show-more-url="zulipUrl || undefined"
		:show-more-text="t('integration_zulip', 'Open Zulip')"
		:loading="loading">
		<template #empty-content>
			<NcEmptyContent
				v-if="!isConnected"
				:name="t('integration_zulip', 'Not connected to Zulip')">
				<template #icon>
					<ZulipIcon :size="64" />
				</template>
				<template #action>
					<NcButton :href="settingsUrl">
						{{ t('integration_zulip', 'Connect account') }}
					</NcButton>
				</template>
			</NcEmptyContent>
			<NcEmptyContent
				v-else-if="error"
				:name="t('integration_zulip', 'Failed to load messages')">
				<template #icon>
					<AlertCircleIcon :size="64" />
				</template>
			</NcEmptyContent>
			<NcEmptyContent
				v-else
				:name="t('integration_zulip', 'No recent messages')">
				<template #icon>
					<ZulipIcon :size="64" />
				</template>
			</NcEmptyContent>
		</template>
	</NcDashboardWidget>
</template>

<script>
import AlertCircleIcon from 'vue-material-design-icons/AlertCircle.vue'

import NcButton from '@nextcloud/vue/components/NcButton'
import NcDashboardWidget from '@nextcloud/vue/components/NcDashboardWidget'
import NcEmptyContent from '@nextcloud/vue/components/NcEmptyContent'

import axios from '@nextcloud/axios'
import { loadState } from '@nextcloud/initial-state'
import { generateUrl } from '@nextcloud/router'

import ZulipIcon from '../components/icons/ZulipIcon.vue'

export default {
	name: 'Dashboard',

	components: {
		AlertCircleIcon,
		NcButton,
		NcDashboardWidget,
		NcEmptyContent,
		ZulipIcon,
	},

	data() {
		const config = loadState('integration_zulip', 'dashboard-config', {})
		return {
			isConnected: config.is_connected ?? false,
			zulipUrl: config.url ?? '',
			messages: [],
			loading: true,
			error: false,
			settingsUrl: generateUrl('/settings/user/connected-accounts'),
			pollInterval: null,
		}
	},

	computed: {
		items() {
			return this.messages.map((msg) => ({
				id: String(msg.id),
				targetUrl: this.getMessageUrl(msg),
				avatarUrl: generateUrl('/apps/integration_zulip/users/{zulipUserId}/image', { zulipUserId: msg.sender_id }),
				avatarUsername: msg.sender_full_name,
				mainText: this.getMessageContent(msg),
				subText: this.getSubText(msg),
			}))
		},
	},

	mounted() {
		if (this.isConnected) {
			this.fetchMessages()
			this.pollInterval = setInterval(this.fetchMessages, 60000)
		} else {
			this.loading = false
		}
	},

	beforeUnmount() {
		if (this.pollInterval) {
			clearInterval(this.pollInterval)
		}
	},

	methods: {
		async fetchMessages() {
			try {
				const response = await axios.get(generateUrl('/apps/integration_zulip/feed'))
				this.messages = response.data
				this.error = false
			} catch (e) {
				this.error = true
				console.error('Error fetching Zulip feed', e)
			} finally {
				this.loading = false
			}
		},

		getMessageContent(msg) {
			const text = (msg.content ?? '').replace(/\n/g, ' ').trim()
			return text.length > 120 ? text.slice(0, 120) + '…' : text
		},

		getSubText(msg) {
			if (msg.type === 'stream') {
				return '#' + msg.display_recipient + ' › ' + (msg.subject || '')
			}
			return t('integration_zulip', 'DM from {name}', { name: msg.sender_full_name })
		},

		getMessageUrl(msg) {
			if (!this.zulipUrl) {
				return '#'
			}
			const base = this.zulipUrl.replace(/\/$/, '')
			if (msg.type === 'private') {
				const userIds = (msg.display_recipient || []).map((r) => r.id).join(',')
				return base + '/#narrow/dm/' + userIds + '/near/' + msg.id
			}
			const topic = encodeURIComponent(msg.subject || '').replace(/%/g, '.')
			return base + '/#narrow/channel/' + msg.stream_id + '/topic/' + topic + '/near/' + msg.id
		},
	},
}
</script>
