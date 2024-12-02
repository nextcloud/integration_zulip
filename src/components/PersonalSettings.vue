<template>
	<div id="zulip_prefs" class="section">
		<h2>
			<ZulipIcon class="icon" />
			{{ t('integration_zulip', 'Zulip integration') }}
		</h2>
		<div id="zulip-content">
			<div id="zulip-connect-block">
				<p class="settings-hint">
					<InformationOutlineIcon :size="24" class="icon" />
					{{ t('integration_zulip', 'You can generate and access your Zulip API key from Personal settings -> Account & privacy -> API key.') }}
				</p>
				<p class="settings-hint">
					{{ t('integration_zulip', 'Then copy the values in the provided zuliprc file into the fields below.') }}
				</p>
				<div class="line">
					<label for="zulip-url">
						<EarthIcon :size="20" class="icon" />
						{{ t('integration_zulip', 'Zulip instance address') }}
					</label>
					<input id="zulip-url"
						v-model="state.url"
						type="text"
						:placeholder="t('integration_zulip', 'Zulip instance address')"
						@input="onInput">
				</div>
				<div class="line">
					<label for="zulip-email">
						<AccountIcon :size="20" class="icon" />
						{{ t('integration_zulip', 'Zulip account email') }}
					</label>
					<input id="zulip-email"
						v-model="state.email"
						type="text"
						:placeholder="t('integration_zulip', 'Zulip account email')"
						@input="onInput">
				</div>
				<div class="line">
					<label for="zulip-key">
						<KeyIcon :size="20" class="icon" />
						{{ t('integration_zulip', 'Zulip API key') }}
					</label>
					<input id="zulip-key"
						v-model="state.api_key"
						type="password"
						:placeholder="t('integration_zulip', 'Zulip API key')"
						@input="onSensitiveInput">
				</div>
			</div>
			<br>
			<NcCheckboxRadioSwitch
				:checked.sync="state.file_action_enabled"
				@update:checked="onCheckboxChanged($event, 'file_action_enabled')">
				{{ t('integration_zulip', 'Add file action to send files to Zulip') }}
			</NcCheckboxRadioSwitch>
			<NcCheckboxRadioSwitch
				:checked.sync="state.search_messages_enabled"
				@update:checked="onCheckboxChanged($event, 'search_messages_enabled')">
				{{ t('integration_zulip', 'Enable searching for messages') }}
			</NcCheckboxRadioSwitch>
		</div>
	</div>
</template>

<script>
import AccountIcon from 'vue-material-design-icons/Account.vue'
import EarthIcon from 'vue-material-design-icons/Earth.vue'
import KeyIcon from 'vue-material-design-icons/Key.vue'
import InformationOutlineIcon from 'vue-material-design-icons/InformationOutline.vue'

import ZulipIcon from './icons/ZulipIcon.vue'

import NcCheckboxRadioSwitch from '@nextcloud/vue/dist/Components/NcCheckboxRadioSwitch.js'

import axios from '@nextcloud/axios'
import { showSuccess, showError } from '@nextcloud/dialogs'
import { loadState } from '@nextcloud/initial-state'
import { confirmPassword } from '@nextcloud/password-confirmation'
import { generateUrl } from '@nextcloud/router'
import { delay } from '../utils.js'

export default {
	name: 'PersonalSettings',

	components: {
		AccountIcon,
		EarthIcon,
		KeyIcon,
		InformationOutlineIcon,
		ZulipIcon,
		NcCheckboxRadioSwitch,
	},

	props: [],

	data() {
		return {
			state: loadState('integration_zulip', 'user-config'),
			loading: false,
		}
	},

	computed: {
	},

	watch: {
	},

	mounted() {
	},

	methods: {
		onCheckboxChanged(newValue, key) {
			this.saveOptions({ [key]: newValue ? '1' : '0' }, true)
		},
		onInput() {
			this.loading = true
			delay(() => {
				this.saveOptions({
					url: this.state.url,
					email: this.state.email,
				})
			}, 2000)()
		},
		onSensitiveInput() {
			this.loading = true
			delay(async () => {
				if (this.state.api_key === 'dummyKey') {
					return
				}

				await confirmPassword()

				this.saveOptions({
					api_key: this.state.api_key,
				})
			}, 2000)()
		},
		saveOptions(values, checkboxChanged = false) {
			const req = {
				values,
			}
			const url = generateUrl(`/apps/integration_zulip/${values.api_key !== undefined ? 'sensitive-' : ''}config`)
			axios.put(url, req)
				.then((response) => {
					showSuccess(t('integration_zulip', 'Zulip options saved'))
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
			width: 250px;
			display: flex;
			align-items: center;
		}
		> input {
			width: 350px;
		}
	}
}
</style>
