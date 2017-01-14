<template>
    <simple-instance-form
            :naming_title="naming_title"
            :task_name_label="task_name_label"
            :task_name_value="task_name_value"
    >
    </simple-instance-form>
</template>

<script>
    import SimpleInstanceForm from './SimpleInstanceForm.vue';

    export default {
        props: [
            'task_name_label', 'task_name_value', 'naming_title'
        ],

        components: {
            SimpleInstanceForm
        }
    }
</script>
