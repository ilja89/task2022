class Submission {

    static findByUserCharon(userId, charonId, then) {
        VueEvent.$emit('show-loader');
        axios.get('/mod/charon/api/charons/' + charonId + '/submissions', { params: { user_id: userId } })
            .then(({data}) => {
                Submission.nextUrl = data.next_page_url;
                then(data.data);
                VueEvent.$emit('hide-loader');
            });
    }

    static getNext(then) {
        VueEvent.$emit('show-loader');
        axios.get(Submission.nextUrl)
            .then(({data}) => {
                Submission.nextUrl = data.next_page_url;
                then(data.data);
                VueEvent.$emit('hide-loader');
            });
    }
}
Submission.nextUrl = null;

export default Submission;
