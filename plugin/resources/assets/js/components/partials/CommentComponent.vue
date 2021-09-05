<template>
    <v-card class="mx-auto" outlined light raised>
        <v-container class="spacing-playground pa-3" fluid>
            <file-comments @updateFromFileComments="updateCommentComponent"
                v-for="file in submission.files"
                v-if="file.comments.length > 0"
                :file="file"
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
    },

    methods: {
        updateCommentComponent() {
            this.$emit('updateFromCommentComponent');
        }
    },
}
</script>

<style scoped>

</style>