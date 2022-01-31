<template>
    <div>
        <v-card v-for="file in this.filesWithReviewComments" :key="file.fileId">
            <div class="review-comments">
                <span class="header">
                    {{ file.path }}
                </span>
                <v-card v-for="reviewComment in file.reviewComments"
                        :key="reviewComment.id"
                        class="review-comment"
                        :class="{ notify: view === 'student' && reviewComment.notify === 1 }"
                >
                    <div class="review-comment-heading">
                        <div class="review-comment-heading-info">
                            <span class="review-comment-author">
                                {{ reviewComment.commentedByFirstName }} {{ reviewComment.commentedByLastName }}
                            </span>
                            <span class="review-comment-date">
                                Comment created: {{ reviewComment.commentCreation }}
                            </span>
                            <span class="review-comment-submission">
                                Submission: {{ file.submissionCreation }}
                            </span>
                            <v-btn v-if="view==='teacher'"
                                   icon
                                   class="remove-button"
                                   @click="deleteReviewComment(reviewComment.id, file.charonId)"
                            >
                                <img src="/mod/charon/pix/bin.png" alt="delete" width="24px">
                            </v-btn>
                        </div>
                    </div>
                    <div class="review-comment-body">
                        <p>
                            {{ reviewComment.reviewComment }}
                        </p>
                    </div>
                </v-card>
            </div>
        </v-card>
    </div>
</template>

<script>

import {ReviewComment} from "../../api";

export default {
    name: "FilesWithReviewComments",
    props: {
        filesWithReviewComments: { required: true },
        view: { required: true }
    },

    methods: {
        deleteReviewComment(reviewCommentId, charonId) {
            if (reviewCommentId === null) {
                return;
            }

            ReviewComment.delete(reviewCommentId, charonId,() => {
                VueEvent.$emit('update-from-review-comment');
                VueEvent.$emit('show-notification', 'Review comment deleted!')
            });
        },
    }
}
</script>

<style scoped>

.header {
    text-align: center;
    line-height: 2;
    padding-left: 1em;
    color: #448aff;
    font-size: 1.5em;
    font-family: Roboto, sans-serif;
}

* {
    box-sizing: border-box;
}

.review-comment-body {
    position: relative;
    font-family: Roboto, sans-serif;
    letter-spacing: .0071428571em;
    font-size: 1em;
    white-space: pre-line;
}

.review-comment-heading {
    font-size: 1em;
}

.review-comment-author {
    color: #448aff;
    padding-right: 0.5em;
    font-weight: normal;
}

.review-comment-date {
    font-size: 1em;
}

.review-comment {
    padding: 0.8em;
    background-color: #f2f3f4!important;
    margin: 0.5em;
}

p {
    margin-bottom: 0!important;
    overflow-wrap: anywhere;
}

.review-comments {
    margin-bottom: 0.5em;
    padding-bottom: 0.5em;
    margin-top: 1em;
}

.remove-button {
    float: right;
}

.review-comment-submission {
    color: #448aff;
    padding-right: 0.5em;
    font-weight: normal;
    margin-left: 1em;
}

.notify {
    background-color: #e6f0ff!important;
}

</style>
