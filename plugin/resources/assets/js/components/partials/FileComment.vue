<template>
    <div>
        <div>
            ({{ comment.created_at }}) {{ comment.teacher.fullname }}: {{ comment.comment }}
        </div>
        <v-btn class="comment-button ma-2" tile outlined color="primary" @click="deleteComment">Delete comment</v-btn>
    </div>
</template>

<script>
import {mapState} from 'vuex'
import CodeReviewComment from "../../api/CodeReviewComment";

export default {
    name: "FileComment",

    props: {
        comment: { required: true },
    },

    computed: {
        ...mapState([
            'charon',
        ])
    },

    methods: {
        deleteComment() {
            if (this.comment === null) {
                return;
            }

            CodeReviewComment.delete(this.comment.id, this.charon.id,() => {
                this.$emit('updateFromFileComment');
                VueEvent.$emit('show-notification', 'Comment deleted')
            });
        },
    }
}
</script>
