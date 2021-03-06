class Preset {

    static save(preset, courseId, then) {
        axios.post('/mod/charon/api/courses/' + courseId + '/presets', {
            preset: preset
        }).then(response => {
            then(response.data)
        }).catch(error => {
            VueEvent.$emit('show-notification', 'Error saving preset.\n' + error, 'danger')
        })
    }

    static update(preset, courseId, then) {
        axios.put('/mod/charon/api/courses/' + courseId + '/presets/' + preset.id, {
            preset: preset
        }).then(response => {
            then(response.data)
        }).catch(error => {
            VueEvent.$emit('show-notification', 'Error updating preset.\n' + error, 'danger')
        })
    }
}

export default Preset
