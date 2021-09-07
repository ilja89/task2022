<template>
    <v-card class="comment">
        <div class="comment-heading">
            <div class="comment-heading-info">
                <span class="comment-author">
                        {{ comment.teacher.fullname }}
                </span>
                <span class="comment-date">
                    {{ comment.created_at }} {{ view }}
                </span>
            </div>
            <div class="comment-actions">
                <v-btn v-if="view==='teacher'" icon class="remove-button" @click="deleteComment">
                    <img src="/mod/charon/pix/bin.png" alt="delete" width="24px">
                </v-btn>
            </div>
        </div>

        <div class="comment-body">
            <p>
                {{ comment.comment }}
            </p>
        </div>

    </v-card>
</template>

<script>
import {mapState} from 'vuex'
import CodeReviewComment from "../../api/CodeReviewComment";

export default {
    name: "FileComment",

    props: {
        comment: { required: true },
        view: {required: true }
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
                VueEvent.$emit('update-from-file-comment');
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
        padding: 10px 20px;
        background-color: #f2f3f4;
        font-family: Roboto, sans-serif;
        letter-spacing: .0071428571em;
    }

    .comment-heading {
        display: flex;
        justify-content: space-between;
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