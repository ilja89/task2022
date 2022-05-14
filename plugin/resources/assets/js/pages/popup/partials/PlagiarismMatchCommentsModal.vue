<template>
    <v-dialog
        v-model="dialog"
        persistent
        max-width="1000px"
        @click:outside="close"
    >
        <template v-slot:activator="{ on, attrs }">
            <v-btn
                @click="showMatchCommentsModal()"
                v-bind="attrs"
                v-on="on"
                icon>
                <v-icon aria-label="Comments" role="button" aria-hidden="false">mdi-comment-multiple-outline</v-icon>
            </v-btn>
        </template>

        <v-card style="background-color: whitesmoke">
            <v-card-title>
                <span class="text-h5">Match status changes</span>
            </v-card-title>
            <div class="mx-auto ma-4" style="max-width: 600px;">
                <v-card class="mt-4 mb-4" v-for="comment in comments" :key="comment.id">
                    <v-card-title>
                        Changed by: {{ comment.author }}
                    </v-card-title>
                    <v-card-subtitle>
                        From {{ comment.old_status }} to {{ comment.new_status }} at {{ comment.created_timestamp }}
                    </v-card-subtitle>
                    <v-card-text>
                        {{ comment.comment }}
                    </v-card-text>
                </v-card>
            </div>
            <v-card-actions style="position: sticky; bottom: 10px; right: 10px;">
                <v-spacer></v-spacer>
                <v-btn
                    color="blue darken-1"
                    text
                    @click="close"
                >
                    Close
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
export default {
    name: "PlagiarismMatchCommentsModal",

    props: {
        comments: {
            required: true
        }
    },

    data() {
        return {
            dialog: false
        }
    },

    methods: {
        showMatchCommentsModal() {
            this.dialog = true
            this.comments = this.comments.sort((a, b) => {
                return new Date(b.created_timestamp) - new Date(a.created_timestamp)
            })
            this.comments.map(comment => {
                comment.created_timestamp = new Date(comment.created_timestamp).toLocaleString()
                return comment
            })
        },

        close() {
            this.dialog = false
        }
    }
}
</script>

<style scoped>

</style>