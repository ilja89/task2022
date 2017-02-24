class Preset {

    static save(preset, courseId, then) {
        VueEvent.$emit('show-loader');
        axios.post('/mod/charon/api/courses/' + courseId + '/presets', {
            preset: preset
        })
            .then(response => {
                VueEvent.$emit('hide-loader');
                then(response.data);
            });
    }

    static update(preset, courseId, then) {
        VueEvent.$emit('show-loader');
        axios.put('/mod/charon/api/courses/' + courseId + '/presets/' + preset.id, {
            preset: preset
        })
            .then(response => {
                VueEvent.$emit('hide-loader');
                then(response.data);
            });
    }
}

export default Preset;
