<template>

    <popup-section
            title="Comments"
            subtitle="Comments are for every Charon and student.">

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
                <input type="text" placeholder="Write a comment..." class="comment-input"
                       v-model="written_comment" @keyup.enter="saveComment">
                <button class="button is-primary" @click="saveComment">COMMENT</button>
            </div>
        </div>

    </popup-section>
</template>

<script>
    import PopupSection from '../partials/PopupSection.vue';
    import ApiCalls from '../../../mixins/apiCalls';
    import Comment from '../../../models/Comment';

    export default {
        mixins: [ ApiCalls ],

        components: { PopupSection },

        props: {
            charon: { required: true },
            student: { required: true }
        },

        data() {
            return {
                written_comment: '',
                comments: []
            };
        },

        watch: {
            charon() {
                this.refreshComments();
            },

            student() {
                this.refreshComments();
            }
        },

        mounted() {
            this.refreshComments();
            VueEvent.$on('refresh-page', () => this.refreshComments());
        },

        methods: {
            saveComment() {
                VueEvent.$emit('show-loader');
                Comment.save(this.written_comment, this.charon.id, this.student.id, comment => {
                    this.comments.push(comment);
                    this.written_comment = '';
                    VueEvent.$emit('hide-loader');
                    VueEvent.$emit('show-notification', 'Comment saved!');
                });
            },

            refreshComments() {
                if (this.charon === null || this.student === null) {
                    this.comments = [];
                    return;
                }

                Comment.all(this.charon.id, this.student.id, comments => {
                    this.comments = comments;
                });
            }
        }
    }
</script>
