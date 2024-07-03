<template>
	<div id="zulip_prefs" class="section">
		<h2>
			<ZulipIcon class="icon" />
			{{ t('integration_zulip', 'Zulip integration') }}
		</h2>
		<p v-if="state.client_id === '' || state.client_secret === ''" class="settings-hint">
			{{ t('integration_zulip', 'The admin must fill in client ID and client secret for you to continue from here') }}
		</p>
		<br>
		<div id="zulip-content">
			<div id="zulip-connect-block">
				<NcButton v-if="!connected"
					id="zulip-connect"
					:disabled="loading === true || state.client_id === '' || state.client_secret === ''"
					:class="{ loading }"
					@click="connectWithOauth">
					<template #icon>
						<OpenInNewIcon />
					</template>
					{{ t('integration_zulip', 'Connect to Zulip') }}
				</NcButton>
				<div v-if="connected" class="line">
					<NcAvatar :url="getUserIconUrl()" :size="48" dispay-name="User" />
					<label class="zulip-connected">
						{{ t('integration_zulip', 'Connected as') }}
						{{ " " }}
						<b>{{ connectedDisplayName }}</b>
					</label>
					<NcButton id="zulip-rm-cred" @click="onLogoutClick">
						<template #icon>
							<CloseIcon />
						</template>
						{{ t('integration_zulip', 'Disconnect from Zulip') }}
					</NcButton>
				</div>
			</div>
			<br>
			<NcCheckboxRadioSwitch
				:checked.sync="state.file_action_enabled"
				@update:checked="onCheckboxChanged($event, 'file_action_enabled')">
				{{ t('integration_zulip', 'Add file action to send files to Zulip') }}
			</NcCheckboxRadioSwitch>
		</div>
	</div>
</template>

<script>
import OpenInNewIcon from 'vue-material-design-icons/OpenInNew.vue'
import CloseIcon from 'vue-material-design-icons/Close.vue'

import ZulipIcon from './icons/ZulipIcon.vue'

import NcAvatar from '@nextcloud/vue/dist/Components/NcAvatar.js'
import NcButton from '@nextcloud/vue/dist/Components/NcButton.js'
import NcCheckboxRadioSwitch from '@nextcloud/vue/dist/Components/NcCheckboxRadioSwitch.js'

import { loadState } from '@nextcloud/initial-state'
import { generateUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'
import { oauthConnect } from '../utils.js'
import { showSuccess, showError } from '@nextcloud/dialogs'

export default {
	name: 'PersonalSettings',

	components: {
		ZulipIcon,
		NcAvatar,
		NcCheckboxRadioSwitch,
		NcButton,
		OpenInNewIcon,
		CloseIcon,
	},

	props: [],

	data() {
		return {
			state: loadState('integration_zulip', 'user-config'),
			loading: false,
		}
	},

	computed: {
		connected() {
			return !!this.state.token && !!this.state.user_id
		},
		connectedDisplayName() {
			return this.state.user_displayname
		},
	},

	watch: {
	},

	mounted() {
		const paramString = window.location.search.substr(1)
		const urlParams = new URLSearchParams(paramString)
		const glToken = urlParams.get('result')
		if (glToken === 'success') {
			showSuccess(t('integration_zulip', 'Successfully connected to Zulip!'))
		} else if (glToken === 'error') {
			showError(t('integration_zulip', 'Error connecting to Zulip:') + ' ' + urlParams.get('message'))
		}
	},

	methods: {
		getUserIconUrl() {
			return generateUrl(
				'/apps/integration_zulip/users/{zulipUserId}/image',
				{ zulipUserId: this.state.user_id },
			) + '?useFallback=1'
		},
		onLogoutClick() {
			this.state.token = ''
			this.state.user_id = ''
			this.state.user_displayname = ''

			this.saveOptions({
				token: '',
				user_id: '',
				user_displayname: '',
			})
		},
		onCheckboxChanged(newValue, key) {
			this.saveOptions({ [key]: newValue ? '1' : '0' }, true)
		},
		saveOptions(values, checkboxChanged = false) {
			const req = {
				values,
			}
			const url = generateUrl('/apps/integration_zulip/config')
			axios.put(url, req)
				.then((response) => {
					if (checkboxChanged) {
						showSuccess(t('integration_zulip', 'Zulip options saved'))
						return
					}

					if (response.data.user_id) {
						this.state.user_id = response.data.user_id
						if (!!this.state.token && !!this.state.user_id) {
							showSuccess(t('integration_zulip', 'Successfully connected to Zulip!'))
							this.state.user_id = response.data.user_id
							this.state.user_displayname = response.data.user_displayname
						} else {
							showError(t('integration_zulip', 'Invalid access token'))
						}
					}
				})
				.catch((error) => {
					showError(
						t('integration_zulip', 'Failed to save Zulip options')
						+ ': ' + (error.response?.request?.responseText ?? ''),
					)
					console.error(error)
				})
				.then(() => {
					this.loading = false
				})
		},
		connectWithOauth() {
			if (this.state.use_popup) {
				oauthConnect(this.state.client_id, null, true)
					.then((data) => {
						this.state.token = 'isset'
						this.state.user_id = data.userId
						this.state.user_displayname = data.userDisplayName
					})
			} else {
				oauthConnect(this.state.client_id, 'settings')
			}
		},
	},
}
</script>

<style scoped lang="scss">
#zulip_prefs {
	#zulip-content {
		margin-left: 40px;
	}

	h2,
	.line,
	.settings-hint {
		display: flex;
		align-items: center;
		.icon {
			margin-right: 4px;
		}
	}

	h2 .icon {
		margin-right: 8px;
	}

	.line {
		width: 450px;
		display: flex;
		align-items: center;
		justify-content: space-between;
	}
}
</style>
