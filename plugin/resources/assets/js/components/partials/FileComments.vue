<template>
    <div>
        {{ file.path }}
        <ul class="file-comments">
            <file-comment
                v-for="comment in file.comments"
                :comment="comment"
            >
            </file-comment>
        </ul>
    </div>
</template>

<script>
import FileComment from "./FileComment";
import CodeReviewComment from "../../api/CodeReviewComment";
import {mapState} from "vuex";
export default {
    ...mapState([
        'charon_id',
        'student_id'
    ]),
    components: {FileComment, CodeReviewComment},

    props: {
        file: { required: true },
    },
    mounted: function () {
      this.$root.$on('refresh_review_comments', (newComment) => {

        console.log("refreshed2")

        this.$forceUpdate();
      })
    }
}
</script>

<style scoped>

</style>