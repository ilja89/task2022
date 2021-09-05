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
export default {
    components: {FileComment},

    props: {
        file: { required: true },
    },
}
</script>

<style scoped>

</style>