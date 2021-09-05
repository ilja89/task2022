<template>
    <div>
        {{ file.path }}
        <ul class="file-comments">
            <file-comment @updateFromFileComment="updateFileComments"
                v-for="comment in file.comments"
                :comment="comment"
            >
            </file-comment>
        </ul>
    </div>
</template>

<script>
import FileComment from "./FileComment";
import CodeReviewComment from "../../api/CodeReviewComment";
import {mapState} from "vuex";

export default {
    ...mapState([
        'charon_id',
        'student_id',
    ]),

    components: {FileComment, CodeReviewComment},

    props: {
        file: { required: true },
    },

    methods: {
        updateFileComments(submissionFileId) {
            console.log("FileComments");
            console.log(submissionFileId);
            this.$forceUpdate();
            this.$emit('updateFromFileComments', submissionFileId);
        }
    }
}
</script>