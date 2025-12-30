<template>
	<div id="zulip_prefs" class="section">
		<h2>
			<ZulipIcon class="icon" />
			{{ t('integration_zulip', 'Zulip integration') }}
		</h2>
		<div id="zulip-content">
			<div id="zulip-connect-block">
				<NcNoteCard type="info">
					{{ t('integration_zulip', 'You can generate and access your Zulip API key from Personal settings -> Account & privacy -> API key.') }}
					<br>
					{{ t('integration_zulip', 'Then copy the values in the provided zuliprc file into the fields below.') }}
				</NcNoteCard>
				<NcTextField
					v-model="state.url"
					:label="t('integration_zulip', 'Zulip instance address')"
					:placeholder="t('integration_zulip', 'Zulip instance address')"
					:show-trailing-button="!!state.url"
					@trailing-button-click="state.url = ''; onInput()"
					@update:model-value="onInput">
					<template #icon>
						<EarthIcon :size="20" />
					</template>
				</NcTextField>
				<NcTextField
					v-model="state.email"
					:label="t('integration_zulip', 'Zulip account email')"
					:placeholder="t('integration_zulip', 'Zulip account email')"
					:show-trailing-button="!!state.email"
					@trailing-button-click="state.email = ''; onInput()"
					@update:model-value="onInput">
					<template #icon>
						<AccountOutlineIcon :size="20" />
					</template>
				</NcTextField>
				<NcTextField
					v-model="state.api_key"
					type="password"
					:label="t('integration_zulip', 'Zulip API key')"
					:placeholder="t('integration_zulip', 'Zulip API key')"
					:show-trailing-button="!!state.api_key"
					@trailing-button-click="state.api_key = ''; onSensitiveInput()"
					@update:model-value="onSensitiveInput">
					<template #icon>
						<KeyOutlineIcon :size="20" />
					</template>
				</NcTextField>
			</div>
			<br>
			<NcFormBox>
				<NcFormBoxSwitch
					v-model="state.file_action_enabled"
					@update:model-value="onCheckboxChanged($event, 'file_action_enabled')">
					{{ t('integration_zulip', 'Add file action to send files to Zulip') }}
				</NcFormBoxSwitch>
				<NcFormBoxSwitch
					v-model="state.search_messages_enabled"
					@update:model-value="onCheckboxChanged($event, 'search_messages_enabled')">
					{{ t('integration_zulip', 'Enable searching for messages') }}
				</NcFormBoxSwitch>
			</NcFormBox>
		</div>
	</div>
</template>

<script>
import AccountOutlineIcon from 'vue-material-design-icons/AccountOutline.vue'
import EarthIcon from 'vue-material-design-icons/Earth.vue'
import KeyOutlineIcon from 'vue-material-design-icons/KeyOutline.vue'

import ZulipIcon from './icons/ZulipIcon.vue'

import NcNoteCard from '@nextcloud/vue/components/NcNoteCard'
import NcFormBox from '@nextcloud/vue/components/NcFormBox'
import NcFormBoxSwitch from '@nextcloud/vue/components/NcFormBoxSwitch'
import NcTextField from '@nextcloud/vue/components/NcTextField'

import axios from '@nextcloud/axios'
import { showSuccess, showError } from '@nextcloud/dialogs'
import { loadState } from '@nextcloud/initial-state'
import { confirmPassword } from '@nextcloud/password-confirmation'
import { generateUrl } from '@nextcloud/router'
import { delay } from '../utils.js'

export default {
	name: 'PersonalSettings',

	components: {
		AccountOutlineIcon,
		EarthIcon,
		KeyOutlineIcon,
		ZulipIcon,
		NcFormBox,
		NcFormBoxSwitch,
		NcTextField,
		NcNoteCard,
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
		display: flex;
		flex-direction: column;
		gap: 4px;
		max-width: 800px;
	}

	h2 {
		display: flex;
		align-items: center;
		gap: 8px;
		justify-content: start;
	}
}
</style>
