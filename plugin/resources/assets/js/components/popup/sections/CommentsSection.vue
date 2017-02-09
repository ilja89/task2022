<template>

    <popup-section
            title="Comments"
            subtitle="Comments are for every Charon and student.">

        <div class="card">

            <div class="comments-container">
                <ul>
                    <li v-for="comment in context.active_comments" class="comment">
                        <span class="comment-author">
                            {{ comment.teacher.firstname }} {{ comment.teacher.lastname }}
                        </span>
                        {{ comment.message }}
                    </li>
                </ul>
            </div>

            <div class="comment-input-container">
                <input type="text" placeholder="Write a comment..." class="comment-input"
                       v-model="written_comment" @keyup.enter="saveComment" @keyup="onCommentWriteStart">
                <button class="button is-primary" @click="saveComment">{{ save_btn_text }}</button>
            </div>
        </div>

    </popup-section>
</template>

<script>
    import PopupSection from '../partials/PopupSection.vue';
    import ApiCalls from '../../../mixins/apiCalls';

    export default {
        mixins: [ ApiCalls ],

        components: { PopupSection },

        props: {
            context: { required: true }
        },

        data() {
            return {
                written_comment: '',
                save_btn_text: 'COMMENT'
            };
        },

        methods: {
            saveComment() {
                VueEvent.$emit('comment-was-saved', this.written_comment);
                this.written_comment = '';
                this.save_btn_text = 'SAVED';
            },

            onCommentWriteStart() {
                if (this.written_comment.length > 0) {
                    this.save_btn_text = 'COMMENT';
                }
            }
        }
    }
</script>
