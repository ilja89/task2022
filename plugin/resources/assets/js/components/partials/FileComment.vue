<template>
    <div>
        ({{ comment.created_at }}) {{ comment.teacher.fullname }}: {{ comment.comment }}
    </div>
</template>

<script>
export default {
    name: "FileComment",

    props: {
        comment: { required: true },
    },
}
</script>

<style scoped>

</style>