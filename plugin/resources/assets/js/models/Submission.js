class Submission {

    static findByUserCharon(userId, charonId, then) {
        axios.get('/mod/charon/api/charons/' + charonId + '/submissions/paginating', { params: { user_id: userId } })
            .then(({data}) => {
                Submission.nextUrl = data.next_page_url;
                then(data.data)
            });
    }

    static getNext(then) {
        axios.get(Submission.nextUrl)
            .then(({data}) => {
                Submission.nextUrl = data.next_page_url;
                then(data.data);
            });
    }
}
Submission.nextUrl = null;

export default Submission;
