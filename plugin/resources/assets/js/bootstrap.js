import Vue from 'vue'
import axios from 'axios'

window.Vue = Vue
window.VueEvent = new Vue()


window.axios = axios
window.axios.defaults.headers.common = {
    'X-Requested-With': 'XMLHttpRequest'
}

// Before request and response show and hide loader
// TODO: Move to separate file?
axios.interceptors.request.use(config => {
    VueEvent.$emit('show-loader')
    return config
}, error => {
    VueEvent.$emit('hide-loader')
    VueEvent.$emit('show-notification', 'Error sending request.', 'danger')
    return Promise.reject(error)
})
axios.interceptors.response.use(response => {
    VueEvent.$emit('hide-loader')
    return response
}, error => {
    VueEvent.$emit('hide-loader')
    return Promise.reject(error)
})

