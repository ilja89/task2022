import {Error} from "./index";

class Preset {

    static save(preset, courseId, then) {
        axios.post('/mod/charon/api/courses/' + courseId + '/presets', {
            preset: preset
        }).then(response => {
            then(response.data)
        }).catch(error => {
            Error.throw(error, 'Error saving preset.\n')
        })
    }

    static update(preset, courseId, then) {
        axios.put('/mod/charon/api/courses/' + courseId + '/presets/' + preset.id, {
            preset: preset
        }).then(response => {
            then(response.data)
        }).catch(error => {
            Error.throw(error, 'Error updating preset.\n')
        })
    }
}

export default Preset
