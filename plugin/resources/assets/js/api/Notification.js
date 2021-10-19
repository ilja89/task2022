class Notification {

    static notifyStudentWhenTeacherComments(studentId, subject, messageText, filePath, charonId, then) {
        axios.post('/mod/charon/api/charons/' + charonId + '/notification/notifyStudent', {
            student_id: studentId,
            subject: subject,
            message_text: messageText,
            file_path: filePath,
            charon_id: charonId
        }).then(response => {
            then(response.data)
        }).catch(error => {
            VueEvent.$emit('show-notification',
                'Something went wrong while sending the notification. \n'
                + error, 'danger')
        })
    }
}

export default Notification