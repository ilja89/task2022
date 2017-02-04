module.exports = {
    methods: {

        getComments(charon_id, student_id, vuePopup) {
            axios.get('/mod/charon/api/charons/' + charon_id + '/comments', {
                params: {
                    student_id: student_id
                }
            })
                .then((response) => {
                    vuePopup.context.active_comments = response.data;
                })
                .catch((error) => {
                    console.log(error);
                });
        },
    }
};
