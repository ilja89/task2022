<template>
    <popup-section title="Comments"
                   subtitle="Comments are for every Charon and student.">

        <template v-if="charonSelector" v-slot:header-right>
            <v-spacer></v-spacer>
            <v-col cols="auto" class="mt-4">
                <v-select
                    v-model="selectedCharon"
                    :items="charons"
                    item-text="name"
                    item-value="id"
                    label="Select"
                    hint="Select a Charon to see the comments"
                    persistent-hint
                    @change="fetchComments"
                ></v-select>
            </v-col>
        </template>

        <v-card class="mx-auto" outlined light raised>
            <v-container class="spacing-playground pa-3" fluid>
                <ul>
                    <li v-for="comment in foundComments" class="comment">
                        <span class="comment-author">
                            {{ comment.teacher.firstname }} {{ comment.teacher.lastname }}
                        </span>
                        {{ comment.message }}
                    </li>
                </ul>
            </v-container>
            <v-container class="spacing-playground pa-3"
                         fluid>

                <v-row>
                    <input type="text" placeholder="Write a comment..."
                           class="comment-input" v-model="writtenComment" @keyup.enter="saveComment">
                    <v-btn class="ma-2" tile outlined color="primary" @click="saveComment">Comment</v-btn>
                </v-row>
            </v-container>
        </v-card>

    </popup-section>
</template>

<script>
import {PopupSection} from '../layouts/index'
import {Comment} from '../../../api/index'

export default {

    components: {PopupSection},

    data() {
        return {
            foundComments: [],
            selectedCharon: null,
            writtenComment: '',
        }
    },

    props: {
        charonSelector: {
            required: false,
            default: false
        },

        studentId: {
            required: true,
        },

        charons: {
            required: true,
            default: []
        }
    },

    methods: {
        saveComment() {
            if (!this.writtenComment.length) {
                return
            }

            Comment.save(this.writtenComment, this.selectedCharon, this.studentId, comment => {
                this.writtenComment = ''
                VueEvent.$emit('show-notification', 'Comment saved!')
                this.fetchComments(this.selectedCharon)
            });
        },

        fetchComments(newCharonId) {
            if (this.studentId && newCharonId) {
                Comment.all(newCharonId, this.studentId, comments => {
                    this.foundComments = comments
                });
            }
        }
    },
}
</script>

<style scoped>
.v-select__selections input {
    width: 0
}
</style>
