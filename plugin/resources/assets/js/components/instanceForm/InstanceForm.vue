<template>
    <advanced-instance-form
            :form="form"
            :grade_types="grade_types"
            :grading_methods="grading_methods"
            :tester_types="tester_types"
    >
    </advanced-instance-form>
</template>

<script>
    import SimpleInstanceForm from './SimpleInstanceForm.vue';
    import AdvancedInstanceForm from './AdvancedInstanceForm.vue';

    export default {
        props: [
            'form', 'grade_types', 'grading_methods', 'tester_types'
        ],

        components: {
            SimpleInstanceForm, AdvancedInstanceForm
        },

        mounted() {
            VueEvent.$on('name-was-changed', (name) => this.form.fields.name = name);
            VueEvent.$on('project-folder-was-changed', (projectFolder) => this.form.fields.project_folder = projectFolder);
            VueEvent.$on('tester-type-was-changed', (tester_type) => this.form.fields.tester_type = tester_type);
        }
    }
</script>
