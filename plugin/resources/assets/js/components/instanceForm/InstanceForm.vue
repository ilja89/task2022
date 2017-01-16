<template>
    <div>
        <advanced-task-info-section
                :form="form"
        ></advanced-task-info-section>

        <advanced-grading-section
                :form="form"
        ></advanced-grading-section>
    </div>
</template>

<script>
    import AdvancedTaskInfoSection from './AdvancedTaskInfoSection.vue';
    import AdvancedGradingSection from './AdvancedGradingSection.vue';

    export default {
        props: [ 'form' ],

        components: { AdvancedTaskInfoSection, AdvancedGradingSection },

        mounted() {
            VueEvent.$on('name-was-changed', (name) => this.form.fields.name = name);
            VueEvent.$on('project-folder-was-changed', (projectFolder) => this.form.fields.project_folder = projectFolder);
            VueEvent.$on('tester-type-was-changed', (tester_type) => this.form.fields.tester_type = tester_type);
            VueEvent.$on('grading-method-was-changed', (grading_method) => this.form.fields.grading_method = grading_method);
        }
    }
</script>
