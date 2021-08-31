<template>
    <v-card class="mx-auto" outlined light raised>
        <v-container class="spacing-playground pa-3" fluid>
            <v-row>
                <input type="text" placeholder="Add a comment..."
                       class="comment-input" v-model="newComment" @keyup.enter="saveComment">
                <v-btn class="ma-2" tile outlined color="primary" @click="saveComment">Comment</v-btn>
            </v-row>
            <div class="student-overview-card">Test comment here...</div>
        </v-container>
    </v-card>
</template>

<script>
export default {
    name: "CommentComponent",

    data() {
        return {
            newComment: '',
            comments: [],
        }
    },


}
</script>

<style scoped>

</style>