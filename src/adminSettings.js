import { createApp } from 'vue'
import AdminSettings from './components/AdminSettings.vue'

const app = createApp(AdminSettings)
app.mixin({ methods: { t, n } })
app.mount('#zulip_prefs')
