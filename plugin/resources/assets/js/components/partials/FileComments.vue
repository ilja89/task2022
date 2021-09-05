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

export default {
    components: {FileComment, CodeReviewComment},

    props: {
        file: { required: true },
    },

    methods: {
        updateFileComments() {
            this.$emit('updateFromFileComments');
        }
    }
}
</script>