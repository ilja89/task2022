<template>
    <div class="review-comments">
        <span class="header">
            {{ file.path }}
        </span>
        <review-comment
            v-for="reviewComment in file.review_comments" :key="reviewComment.id"
            :reviewComment="reviewComment" :view="view"
        >
        </review-comment>
    </div>
</template>

<script>
import ReviewComment from "./ReviewComment";

export default {

    components: {ReviewComment},

    props: {
        file: { required: true },
        view: { required: true }
    },
}
</script>

<style scoped>
    .review-comments {
        color: #448aff;
    }

    .header {
        text-align: center;
        line-height: 2;
        padding-left: 33px;

        color: #448aff;
        font-size: 20px;
        font-family: Roboto, sans-serif;
    }
</style>
