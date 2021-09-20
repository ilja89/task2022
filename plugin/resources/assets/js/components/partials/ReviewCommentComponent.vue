<template>
    <v-card>
        <v-container>
            <review-comments
                v-for="file in submission.files"
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
        submission: { required: true },
        view: {required: true }
    },
}
</script>
