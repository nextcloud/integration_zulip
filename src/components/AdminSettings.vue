<template>
	<div id="zulip_prefs" class="section">
		<h2>
			<ZulipIcon class="icon" />
			{{ t('integration_zulip', 'Zulip integration') }}
		</h2>
		<p class="settings-hint">
			<span>
				{{ t('integration_zulip', 'To allow your Nextcloud users to use OAuth to authenticate to the Zulip app, and set the ID and secret here.') }}
				{{ " " }}
				<a href="https://api.zulip.com/apps">{{ t('integration_zulip', 'Link to create a Zulip application') }}</a>
			</span>
		</p>
		<br>
		<p class="settings-hint">
			<InformationOutlineIcon :size="24" class="icon" />
			{{ t('integration_zulip', 'Make sure you set the "Redirect URI" in the "OAuth & Permissions" section of your Zulip app settings to') }}
		</p>
		<strong>{{ redirect_uri }}</strong>
		<br><br>
		<p class="settings-hint">
			{{ t('integration_zulip', 'Put the "Client ID" and "Client secret" below. Your Nextcloud users will then see a "Connect to Zulip" button in their personal settings.') }}
		</p>
		<p class="settings-hint">
			{{ t('integration_zulip', 'In the same "OAuth & Permissions" section, token rotation can be enabled. If enabled, your access token would be regularly refreshed with a refresh token. This is handled automatically.') }}
		</p>
		<br>
		<div id="zulip-content">
			<div class="line">
				<label for="zulip-client-id">
					<KeyIcon :size="20" class="icon" />
					{{ t('integration_zulip', 'Client ID') }}
				</label>
				<input id="zulip-client-id"
					v-model="state.client_id"
					type="password"
					:readonly="readonly"
					:placeholder="t('integration_zulip', 'ID of your Zulip application')"
					@input="onInput"
					@focus="readonly = false">
			</div>
			<div class="line">
				<label for="zulip-client-secret">
					<KeyIcon :size="20" class="icon" />
					{{ t('integration_zulip', 'Application secret') }}
				</label>
				<input id="zulip-client-secret"
					v-model="state.client_secret"
					type="password"
					:readonly="readonly"
					:placeholder="t('integration_zulip', 'Client secret of your Zulip application')"
					@focus="readonly = false"
					@input="onInput">
			</div>
			<NcCheckboxRadioSwitch
				:checked.sync="state.use_popup"
				@update:checked="onUsePopupChanged">
				{{ t('integration_zulip', 'Use a popup to authenticate') }}
			</NcCheckboxRadioSwitch>
		</div>
	</div>
</template>

<script>
import InformationOutlineIcon from 'vue-material-design-icons/InformationOutline.vue'
import KeyIcon from 'vue-material-design-icons/Key.vue'

import NcCheckboxRadioSwitch from '@nextcloud/vue/dist/Components/NcCheckboxRadioSwitch.js'
import { loadState } from '@nextcloud/initial-state'
import { generateUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'
import { showSuccess, showError } from '@nextcloud/dialogs'
import ZulipIcon from './icons/ZulipIcon.vue'

import { delay } from '../utils.js'

export default {
	name: 'AdminSettings',

	components: {
		ZulipIcon,
		NcCheckboxRadioSwitch,
		InformationOutlineIcon,
		KeyIcon,
	},

	props: [],

	data() {
		return {
			state: loadState('integration_zulip', 'admin-config'),
			// to prevent some browsers to fill fields with remembered passwords
			readonly: true,
			redirect_uri: window.location.protocol + '//' + window.location.host + generateUrl('/apps/integration_zulip/oauth-redirect'),
		}
	},

	watch: {
	},

	mounted() {
	},

	methods: {
		onUsePopupChanged(newValue) {
			this.saveOptions({ use_popup: newValue ? '1' : '0' })
		},
		onInput() {
			delay(() => {
				this.saveOptions({
					client_id: this.state.client_id,
					client_secret: this.state.client_secret,
				})
			}, 2000)()
		},
		saveOptions(values) {
			const req = {
				values,
			}
			const url = generateUrl('/apps/integration_zulip/admin-config')
			axios.put(url, req).then(() => {
				showSuccess(t('integration_zulip', 'Zulip admin options saved'))
			}).catch((error) => {
				showError(
					t('integration_zulip', 'Failed to save Zulip admin options')
					+ ': ' + (error.response?.request?.responseText ?? ''),
				)
				console.error(error)
			})
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
		> label {
			width: 300px;
			display: flex;
			align-items: center;
		}
		> input {
			width: 300px;
		}
	}

	a {
		color: #006196;
	}
}
</style>
