<template>
    <popup-section
        title="Comments"
        subtitle="Comments are for every Charon and student."
    >

        <div class="card">
            <div class="comments-container">
                <ul>
                    <li v-for="comment in comments" class="comment">
                        <span class="comment-author">
                            {{ comment.teacher.firstname }} {{ comment.teacher.lastname }}
                        </span>
                        {{ comment.message }}
                    </li>
                </ul>
            </div>

            <div class="comment-input-container">
                <input
                    type="text"
                    placeholder="Write a comment..."
                    class="comment-input"
                    v-model="writtenComment"
                    @keyup.enter="saveComment"
                >
                <button class="button is-primary" @click="saveComment">COMMENT</button>
            </div>
        </div>

    </popup-section>
</template>

<script>
    import { mapState } from 'vuex'
    import { PopupSection } from '../layouts/index'
    import { Comment } from '../../../api/index'

    export default {

        components: { PopupSection },

        data() {
            return {
                writtenComment: '',
                comments: [],
            }
        },

        computed: {
            ...mapState([
                'charon',
                'student',
            ]),
        },

        watch: {
            charon() {
                this.refreshComments()
            },

            student() {
                this.refreshComments()
            },
        },

        mounted() {
            this.refreshComments()
            VueEvent.$on('refresh-page', () => this.refreshComments())
        },

        methods: {
            saveComment() {
                if (this.writtenComment.length === 0) {
                    return
                }

                Comment.save(this.writtenComment, this.charon.id, this.student.id, comment => {
                    this.comments.push(comment)
                    this.writtenComment = ''
                    VueEvent.$emit('show-notification', 'Comment saved!')
                });
            },

            refreshComments() {
                if (this.charon === null || this.student === null) {
                    this.comments = []
                    return
                }

                Comment.all(this.charon.id, this.student.id, comments => {
                    this.comments = comments
                })
            },
        },
    }
</script>
