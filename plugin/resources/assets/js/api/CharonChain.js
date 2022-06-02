class CharonChain {
    static testFunc(charoId, then) {
        axios.get('/mod/charon/api/charons/' + charoId + '/charonChain')
            .then(response => {
                then(response.data)
        }).catch(error => {
            VueEvent.$emit('show-notification', 'Error retrieving charon chain. \n' + error, 'danger')
        })
    }
}

export default CharonChain