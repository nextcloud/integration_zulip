<template>
	<div class="zulip-settings-dialog-container">
		<NcDialog
			v-if="show"
			:name="t('integration_zulip', 'Connect to Zulip')"
			@closing="closeDialog">
			<template #actions>
				<NcButton @click="closeDialog">
					{{ t('integration_zulip', 'Cancel') }}
				</NcButton>
				<NcButton variant="primary" :href="settingsLink">
					{{ t('integration_zulip', 'Go to settings') }}
				</NcButton>
			</template>
			<p>{{ t('integration_zulip', 'You must connect a Zulip account to perform this operation.') }}</p>
			<p>{{ t('integration_zulip', 'Go to "Connected accounts" personal settings?') }}</p>
			<br>
		</NcDialog>
	</div>
</template>

<script>
import NcButton from '@nextcloud/vue/components/NcButton'
import NcDialog from '@nextcloud/vue/components/NcDialog'

import { generateUrl } from '@nextcloud/router'

export default {
	name: 'GoToSettingsDialog',

	components: {
		NcButton,
		NcDialog,
	},

	data() {
		return {
			show: false,
			settingsLink: generateUrl('/settings/user/connected-accounts'),
		}
	},

	computed: {
	},

	watch: {
	},

	mounted() {
	},

	methods: {
		showDialog() {
			this.show = true
		},
		closeDialog() {
			this.show = false
			this.$el.dispatchEvent(new CustomEvent('closing', { bubbles: true }))
		},
	},
}
</script>

<style scoped lang="scss">
// nothing yet
</style>
