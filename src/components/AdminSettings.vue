<template>
	<div id="zulip_prefs" class="section">
		<h2>
			<ZulipIcon class="icon" />
			{{ t('integration_zulip', 'Zulip integration') }}
		</h2>
		<div id="zulip-content">
			<h3>{{ t('integration_zulip', 'File sharing options') }}</h3>
			<p class="settings-hint">
				{{ t('integration_zulip', 'Configure which sharing options are available to users when sending files to Zulip.') }}
			</p>
			<NcFormBox>
				<NcFormBoxSwitch
					v-model="state.send_type_enabled_file"
					:disabled="isOnlyEnabled('file')"
					@update:model-value="onCheckboxChanged($event, 'send_type_enabled_file')">
					{{ t('integration_zulip', 'Allow uploading files') }}
				</NcFormBoxSwitch>
				<NcFormBoxSwitch
					v-model="state.send_type_enabled_public_link"
					:disabled="isOnlyEnabled('public_link')"
					@update:model-value="onCheckboxChanged($event, 'send_type_enabled_public_link')">
					{{ t('integration_zulip', 'Allow sending public links') }}
				</NcFormBoxSwitch>
				<NcFormBoxSwitch
					v-model="state.send_type_enabled_internal_link"
					:disabled="isOnlyEnabled('internal_link')"
					@update:model-value="onCheckboxChanged($event, 'send_type_enabled_internal_link')">
					{{ t('integration_zulip', 'Allow sending internal links') }}
				</NcFormBoxSwitch>
			</NcFormBox>
			<div v-if="enabledSendTypes.length > 1" class="default-type-section">
				<h3>{{ t('integration_zulip', 'Default sharing option') }}</h3>
				<p class="settings-hint">
					{{ t('integration_zulip', 'Select which option is pre-selected when users open the sharing dialog.') }}
				</p>
				<div class="default-type-radios">
					<NcCheckboxRadioSwitch
						v-model="defaultSendType"
						value="first_available"
						name="default_send_type_radio"
						type="radio">
						{{ t('integration_zulip', 'First available option') }}
					</NcCheckboxRadioSwitch>
					<NcCheckboxRadioSwitch
						v-if="state.send_type_enabled_file"
						v-model="defaultSendType"
						value="file"
						name="default_send_type_radio"
						type="radio">
						{{ t('integration_zulip', 'Upload files') }}
					</NcCheckboxRadioSwitch>
					<NcCheckboxRadioSwitch
						v-if="state.send_type_enabled_public_link"
						v-model="defaultSendType"
						value="public_link"
						name="default_send_type_radio"
						type="radio">
						{{ t('integration_zulip', 'Public links') }}
					</NcCheckboxRadioSwitch>
					<NcCheckboxRadioSwitch
						v-if="state.send_type_enabled_internal_link"
						v-model="defaultSendType"
						value="internal_link"
						name="default_send_type_radio"
						type="radio">
						{{ t('integration_zulip', 'Internal links') }}
					</NcCheckboxRadioSwitch>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import ZulipIcon from './icons/ZulipIcon.vue'

import NcFormBox from '@nextcloud/vue/components/NcFormBox'
import NcFormBoxSwitch from '@nextcloud/vue/components/NcFormBoxSwitch'
import NcCheckboxRadioSwitch from '@nextcloud/vue/components/NcCheckboxRadioSwitch'

import axios from '@nextcloud/axios'
import { showSuccess, showError } from '@nextcloud/dialogs'
import { loadState } from '@nextcloud/initial-state'
import { generateUrl } from '@nextcloud/router'

export default {
	name: 'AdminSettings',

	components: {
		ZulipIcon,
		NcFormBox,
		NcFormBoxSwitch,
		NcCheckboxRadioSwitch,
	},

	data() {
		return {
			state: loadState('integration_zulip', 'admin-config'),
		}
	},

	computed: {
		enabledSendTypes() {
			return [
				this.state.send_type_enabled_file && 'file',
				this.state.send_type_enabled_public_link && 'public_link',
				this.state.send_type_enabled_internal_link && 'internal_link',
			].filter(Boolean)
		},
		defaultSendType: {
			get() {
				return this.state.send_type_default === '' ? 'first_available' : this.state.send_type_default
			},
			set(value) {
				this.onDefaultTypeChanged(value === 'first_available' ? '' : value)
			},
		},
	},

	watch: {
		enabledSendTypes(newTypes) {
			if (this.state.send_type_default !== '' && !newTypes.includes(this.state.send_type_default)) {
				this.onDefaultTypeChanged('')
			}
		},
	},

	methods: {
		isOnlyEnabled(key) {
			return this.enabledSendTypes.length === 1 && this.enabledSendTypes[0] === key
		},
		onCheckboxChanged(newValue, key) {
			this.saveAdminConfig({ [key]: newValue ? '1' : '0' })
		},
		onDefaultTypeChanged(value) {
			this.state.send_type_default = value
			this.saveAdminConfig({ send_type_default: value })
		},
		saveAdminConfig(values) {
			const req = { values }
			const url = generateUrl('/apps/integration_zulip/admin-config')
			axios.put(url, req)
				.then(() => {
					showSuccess(t('integration_zulip', 'Zulip admin options saved'))
				})
				.catch((error) => {
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

	h3 {
		margin-top: 16px;
		margin-bottom: 4px;
	}

	.settings-hint {
		color: var(--color-text-maxcontrast);
		margin-bottom: 8px;
	}

	.default-type-section {
		margin-top: 16px;
	}

	.default-type-radios {
		margin-left: 10px;
		display: flex;
		flex-direction: column;
		gap: 4px;
	}
}
</style>
