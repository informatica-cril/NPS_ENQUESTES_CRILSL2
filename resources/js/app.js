import './bootstrap';
import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from '../../frontend/src/App.vue';
import router from '../../frontend/src/router';
import '../../frontend/src/assets/css/main.css';

const app = createApp(App);
app.use(createPinia());
app.use(router);
app.mount('#app');
