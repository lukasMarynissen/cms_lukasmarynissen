import Vue from 'vue';

import './assets/scss/style.scss';


import { store } from './store';
import { router } from './utils';
import App from './App';

new Vue({
    el: '#app',
    router,
    store,
    render: h => h(App)
});