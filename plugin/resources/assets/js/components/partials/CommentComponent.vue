<template>
    <v-card>
        <v-container>
            <file-comments @updateFromFileComments="updateCommentComponent"
                v-for="file in submission.files"
                v-if="file.comments.length > 0"
                :file="file" :view="view"
            >
            </file-comments>
        </v-container>
    </v-card>
</template>

<script>
import FileComments from "./FileComments";

export default {
    components: {FileComments},

    props: {
        submission: { required: true },
        view: {required: true }
    },

    methods: {
        updateCommentComponent() {
            this.$emit('updateFromCommentComponent');
        }
    },
}
</script>
