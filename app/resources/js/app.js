/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');

//Register vue.js plugins
import VueRouter from 'vue-router'
import VueEllipseProgress from 'vue-ellipse-progress';
import { BootstrapVue, IconsPlugin } from 'bootstrap-vue';
import '../sass/app.scss';

Vue.use(VueRouter)
Vue.use(VueEllipseProgress);
Vue.use(BootstrapVue)
Vue.use(IconsPlugin)

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

import App from '../js/components/App';
import Home from '../js/components/Home';
import Slideshow from '../js/components/Slideshow';
import RemoteControl from '../js/components/RemoteControl';

const router = new VueRouter({
    mode: "history",
    linkActiveClass: "active",
    linkExactActiveClass: "exact-active",
    routes: [
        {
            path: "/",
            name: "home",
            component: Home
        },
        {
            path: "/slideshow",
            name: "slideshow",
            component: Slideshow
        },
        {
            path: "/remote-control",
            name: "remote control",
            component: RemoteControl
        },
    ]
});

const app = new Vue({
    el: '#app',
    components: {
        App
    },
    router
});
