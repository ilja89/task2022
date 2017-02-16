class Charon {

    static all(courseId, then) {
        axios.get('/mod/charon/api/courses/' + courseId + '/charons')
            .then(response => then(response.data));
    }
}

export default Charon;
