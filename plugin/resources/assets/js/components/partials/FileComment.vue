<template>
    <v-card class="comment">
        <div class="comment-heading">
            <span class="comment-author">
                    {{ comment.teacher.fullname }}
            </span>
            <span class="comment-date">
                {{ comment.created_at }}
            </span>

        </div>
        <div class="comment-body">
            <p>
                {{ comment.comment }}
            </p>
        </div>
        <v-btn class="comment-button ma-2" tile outlined color="primary" @click="deleteComment">Delete comment</v-btn>
    </v-card>
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

<style scoped>
    * {
        box-sizing: border-box;
    }

    .comment {
        position: relative;
        margin: 1px 20px;
        background-color: #f2f3f4;
        font-family: Roboto, sans-serif;
        letter-spacing: .0071428571em;
    }

    .comment-heading {
        display: flex;
        align-items: flex-end;
        height: 30px;
        font-size: 14px;
    }

    .comment-author {
        color: #448aff;
        padding-right: 10px;
        margin-left: 10px;
        font-weight: normal;
    }

    .comment-date {
        font-size: 12px;
    }

    .comment-body {
        font-size: 14px;
    }

    p {
        white-space: pre-line;
    }
</style>