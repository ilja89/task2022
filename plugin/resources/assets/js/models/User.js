class User {

    static findById(courseId, userId, then) {
        VueEvent.$emit('show-loader');
        axios.get('/mod/charon/api/courses/' + courseId + '/users/' + userId)
            .then(({data}) => {
                then(data);
                VueEvent.$emit('hide-loader');
            });
    }

    static getReportTable(courseId, userId, then) {
        VueEvent.$emit('show-loader');
        axios.get('/mod/charon/api/courses/' + courseId + '/users/' + userId + '/report-table')
            .then(({data}) => {
                then(data);
                VueEvent.$emit('hide-loader');
            });
    }
}

export default User;
