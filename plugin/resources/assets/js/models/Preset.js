class Preset {

    static save(preset, courseId, then) {
        axios.post('/mod/charon/api/courses/' + courseId + '/presets', {
            preset: preset
        }).then(response => {
            then(response.data)
        }).catch(error => {
            VueEvent.$emit('show-notification', 'Error saving preset.', 'danger')
        })
    }

    static update(preset, courseId, then) {
        axios.put('/mod/charon/api/courses/' + courseId + '/presets/' + preset.id, {
            preset: preset
        }).then(response => {
            then(response.data)
        }).catch(error => {
            VueEvent.$emit('show-notification', 'Error updating preset.', 'danger')
        })
    }
}

export default Preset
