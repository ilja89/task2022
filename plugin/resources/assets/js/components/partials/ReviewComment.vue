<template>
    <v-card class="review-comment">
        <div class="review-comment-heading">
            <div class="comment-heading-info">
                <span class="review-comment-author">
                        {{ reviewComment.teacher.fullname }}
                </span>
                <span class="review-comment-date">
                    {{ reviewComment.created_at }}
                </span>
            </div>
            <div class="review-comment-actions">
                <v-btn v-if="view==='teacher'" icon class="remove-button" @click="deleteReviewComment">
                    <img src="/mod/charon/pix/bin.png" alt="delete" width="24px">
                </v-btn>
            </div>
        </div>

        <div class="review-comment-body">
            <p>
                {{ reviewComment.comment }}
            </p>
        </div>

    </v-card>
</template>

<script>
import {mapState} from 'vuex'
import ReviewComment from "../../api/ReviewComment";

export default {
    name: "ReviewComment",

    props: {
        reviewComment: { required: true },
        view: {required: true }
    },

    computed: {
        ...mapState([
            'charon',
        ])
    },

    methods: {
        deleteReviewComment() {
            if (this.reviewComment === null) {
                return;
            }

            ReviewComment.delete(this.reviewComment.id, this.charon.id,() => {
                VueEvent.$emit('update-from-review-comment');
                VueEvent.$emit('show-notification', 'Comment deleted')
            });
        },
    }
}
</script>

<style scoped>
    * {
        box-sizing: border-box;
    }

    .review-comment {
        position: relative;
        padding: 10px 20px;
        background-color: #f2f3f4;
        font-family: Roboto, sans-serif;
        letter-spacing: .0071428571em;
    }

    .review-comment-heading {
        display: flex;
        justify-content: space-between;
        height: 30px;
        font-size: 14px;
    }

    .review-comment-author {
        color: #448aff;
        padding-right: 10px;
        margin-left: 10px;
        font-weight: normal;
    }

    .review-comment-date {
        font-size: 12px;
    }

    .review-comment-body {
        font-size: 14px;
    }

    p {
        white-space: pre-line;
    }

</style>