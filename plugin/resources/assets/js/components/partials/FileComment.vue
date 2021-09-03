<template>
    <div class="comment">
        <div class="comment-heading">
            <span class="comment-author">
                    {{ comment.teacher.fullname }}
                </span>
            {{ comment.created_at }}
        </div>
        <div class="comment-body">
            <p>
                {{ comment.comment }}
            </p>
        </div>
    </div>
</template>

<script>
export default {
    name: "FileComment",

    props: {
        comment: { required: true },
    },
}
</script>

<style scoped>
    * {
        box-sizing: border-box;
    }

    .comment {
        position: relative;
        margin: 20px auto;
    }

    .comment-heading {
        display: flex;
        align-items: flex-end;
        height: 50px;
        font-size: 14px;
    }

    .comment-author {
        color: rgba(0, 0, 0, 0.85);
        font-weight: bold;
        text-decoration: none;
        padding-right: 10px;
        margin-left: 10px;
    }

    .comment-body {
        white-space: pre-line;
    }
</style>