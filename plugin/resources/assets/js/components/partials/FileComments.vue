<template>
    <div class="file-comments">
        <span class="comment-header">
            {{ file.path }}
        </span>
        <file-comment
            v-for="comment in file.comments"
            :comment="comment" :view="view"
        >
        </file-comment>
    </div>
</template>

<script>
import FileComment from "./FileComment";
import CodeReviewComment from "../../api/CodeReviewComment";

export default {

    components: {FileComment, CodeReviewComment},

    props: {
        file: { required: true },
        view: { required: true }
    },
}
</script>

<style scoped>
    .file-comments {
        color: #448aff;
    }

    .comment-header {
        text-align: center;
        line-height: 2;
        padding-left: 33px;

        color: #448aff;
        font-size: 20px;
        font-family: Roboto, sans-serif;
    }
</style>