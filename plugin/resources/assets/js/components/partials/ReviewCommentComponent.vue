<template>
    <v-card>
        <v-container>
            <review-comments
                v-for="file in files" key="file.id"
                v-if="file.review_comments.length > 0"
                :file="file" :view="view"
            >
            </review-comments>
        </v-container>
    </v-card>
</template>

<script>
import ReviewComments from "./ReviewComments";

export default {
    components: {ReviewComments},

    props: {
        files: { required: true },
        view: {required: true }
    },
}
</script>
