<template>
    <h1 class="title is-3 page-title  bottom-border-separator">
        {{ current_title }}
    </h1>
</template>

<script>
    export default {
        props: {
            title: {
                required: false,
                default: ''
            },
            student: { required: false }
        },

        computed: {
            current_title() {
                if (this.student !== null) {
                    return this.student.firstname + ' ' + this.student.lastname;
                } else {
                    return this.title;
                }
            }
        }
    }
</script>
