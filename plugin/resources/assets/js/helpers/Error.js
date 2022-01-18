class Error {

    static throwWithCheck(error, errorText) {
        VueEvent.$emit('show-notification',
            error.response && error.response.data && error.response.data.title
                ? error.response.data.title + ' ' + error.response.data.detail
                : errorText + error, 'danger');
    }

    static throw(error, errorText) {
        VueEvent.$emit('show-notification', errorText + error, 'danger')
    }
}

export default Error