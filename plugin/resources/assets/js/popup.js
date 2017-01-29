import './bootstrap';
import PopupHeader from './components/popup/PopupHeader.vue';
import PopupNavigation from './components/popup/PopupNavigation.vue';
import PopupContent from './components/popup/PopupContent.vue';
import Loader from './components/popup/partials/Loader.vue';

import NavigationLink from './components/popup/partials/NavigationLink.vue';

window.VueEvent = new Vue();

const app = new Vue({
    el: '#app',
    components: { PopupHeader, PopupNavigation, PopupContent, Loader },
    data: { }
});

