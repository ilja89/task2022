<template>
    <v-card>
        <v-container>
            <file-comments
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
}
</script>

<style scoped>

</style>