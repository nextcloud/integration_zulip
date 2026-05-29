import { translate, translatePlural } from '@nextcloud/l10n'
import { createApp } from 'vue'

import Dashboard from './views/Dashboard.vue'

document.addEventListener('DOMContentLoaded', () => {
	OCA.Dashboard.register('zulip_feed', (el, { widget }) => {
		const app = createApp(Dashboard, { title: widget.title })
		app.config.globalProperties.t = translate
		app.config.globalProperties.n = translatePlural
		app.mount(el)
	})
})
