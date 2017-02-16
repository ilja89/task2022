class Charon {

    static all(courseId, then) {
        VueEvent.$emit('show-loader');
        axios.get('/mod/charon/api/courses/' + courseId + '/charons')
            .then(response => {
                then(response.data);
                VueEvent.$emit('hide-loader');
            });
    }
}

export default Charon;
