<template>
    <div class="file-comments">
        <span class="comment-header">
            {{ file.path }} - <!--{{ // translation does not work in popup -->translate('commentsText') <!--}}-->
        </span>
        <file-comment
            v-for="comment in file.comments"
            :comment="comment"
        >
        </file-comment>
    </div>
</template>

<script>
import FileComment from "./FileComment";
import {Translate} from "../../mixins";
export default {

    mixins: [Translate],

    components: {FileComment},

    props: {
        file: { required: true },
    },
}
</script>

<style scoped>
    .file-comments {
        padding: 10px;
    }

    .comment-header {
        color: #bbbbbb;
        font-size: 28px;
        font-weight: bold;
    }
</style>