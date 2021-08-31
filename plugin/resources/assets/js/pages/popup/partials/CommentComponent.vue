<template>
    <v-card class="mx-auto" outlined light raised>
        <v-container class="spacing-playground pa-3" fluid>
            <v-row>
                <input type="text" placeholder="Add a comment..."
                       class="comment-input" v-model="newComment" @keyup.enter="saveComment">
                <v-btn class="ma-2" tile outlined color="primary" @click="saveComment">Comment</v-btn>
            </v-row>
            <div class="student-overview-card">Test comment here...</div>
        </v-container>
    </v-card>
</template>

<script>
import {mapState} from "vuex";
import SubmissionComment from "../../../api/SubmissionComment";

export default {
    name: "CommentComponent",

    data() {
        return {
            newComment: '',
            comments: [],
        }
    },

    computed: {
        ...mapState([
            'submission',
        ]),
    },

    methods: {
        saveComment() {
            if (this.newComment === null || this.newComment.length === 0) {
                return
            }

            SubmissionComment.save(this.newComment, this.submission, comment => {
                this.comments.push(comment)
                this.writtenComment = ''
                VueEvent.$emit('show-notification', 'Comment saved!')
            });
        },

        refreshComments() {
            if (this.charon === null || this.student === null) {
                this.comments = []
                return
            }

            Comment.all(this.charon.id, this.student.id, comments => {
                this.comments = comments
            })
        },
    },

}
</script>

<style scoped>

</style>