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

        data() {
            return {
                current_title: ''
            }
        },

        mounted() {
            if (this.student !== null) {
                this.current_title = this.student.firstname + ' ' + this.student.lastname;
            } else {
                this.current_title = this.title;
            }
        }
    }
</script>
