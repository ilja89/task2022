class Email {

    // send email from currently logged in teacher to specified user, Moodle emails will be used
    static sendEmailFromTeacherToStudent(studentId, subject, messageText, messageHtml, charonId, then) {
        axios.post('/mod/charon/api/charons/' + charonId + '/email/sendFromTeacherToStudent', {
            student_id: studentId,
            subject: subject,
            message_text: messageText,
            message_html: messageHtml,
        }).then(response => {
            then(response.data)
        }).catch(error => {
            VueEvent.$emit('show-notification',
                'Something went wrong with sending the user an email. Make sure all required values are set. \n'
                + error, 'danger')
        })
    }
}

export default Email