<template>
    <v-card class="mx-auto" outlined light raised>
        <v-container class="spacing-playground pa-3" fluid>
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
  mounted: function () {
    this.$root.$on('refresh_review_comments', (newComment) => {

      console.log("refreshed1")

      this.$forceUpdate();
    })
  }
}
</script>

<style scoped>

</style>