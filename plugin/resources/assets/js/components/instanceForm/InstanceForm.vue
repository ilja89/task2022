<template>
    <div>
        <instance-form-fieldset
                toggle_id="tgl1"
                @advanced-was-toggled="toggleAdvancedInfoSection">

            <template slot="title">{{ translate('task_info_title') }}</template>

            <slot>
                <advanced-task-info-section
                        v-if="advanced_info_section_active"
                        :form="form"
                ></advanced-task-info-section>
                <simple-task-info-section
                        v-else
                        :form="form"
                ></simple-task-info-section>
            </slot>
        </instance-form-fieldset>

        <instance-form-fieldset
                toggle_id="tgl2"
                @advanced-was-toggled="toggleAdvancedGradingSection">

            <template slot="title">{{ translate('grading_title') }}</template>

            <slot>
                <advanced-grading-section
                        v-if="advanced_grading_section_active"
                        :form="form"
                ></advanced-grading-section>
                <simple-grading-section
                        v-else
                        :form="form"
                >
                </simple-grading-section>
            </slot>
        </instance-form-fieldset>
    </div>
</template>

<script>
    import AdvancedTaskInfoSection from './AdvancedTaskInfoSection.vue';
    import AdvancedGradingSection from './AdvancedGradingSection.vue';
    import SimpleTaskInfoSection from './SimpleTaskInfoSection.vue';
    import SimpleGradingSection from './SimpleGradingSection.vue';

    import InstanceFormFieldset from '../form/InstanceFormFieldset.vue';

    import Translate from '../../mixins/translate';

    export default {
        mixins: [ Translate ],

        props: {
            form: { required: true }
        },

        components: {
            SimpleTaskInfoSection, SimpleGradingSection,
            AdvancedTaskInfoSection, AdvancedGradingSection, InstanceFormFieldset
        },

        data() {
            return {
                advanced_info_section_active: true,
                advanced_grading_section_active: true
            }
        },

        methods: {
            toggleAdvancedInfoSection(advanced_toggle) {
                this.advanced_info_section_active = advanced_toggle;
            },

            toggleAdvancedGradingSection(advanced_toggle) {
                this.advanced_grading_section_active = advanced_toggle;
            }
        },

        mounted() {
            VueEvent.$on('name-was-changed', (name) => this.form.fields.name = name);
            VueEvent.$on('project-folder-was-changed', (projectFolder) => this.form.fields.project_folder = projectFolder);
            VueEvent.$on('extra-was-changed', (extra) => this.form.fields.extra = extra);
            VueEvent.$on('tester-type-was-changed', (tester_type) => this.form.fields.tester_type = tester_type);
            VueEvent.$on('grading-method-was-changed', (grading_method) => this.form.fields.grading_method = grading_method);

            VueEvent.$on('grade-type-was-activated', (activated_grade_type_code) => {
                this.form.activateGrademap(activated_grade_type_code);
            });
            VueEvent.$on('grade-type-was-deactivated', (deactivated_grade_type_code) => {
                this.form.deactivateGrademap(deactivated_grade_type_code);
            });
            VueEvent.$on('deadline-was-added', () => {
                this.form.addDeadline();
            });
            VueEvent.$on('deadline-was-removed', (id) => {
                this.form.fields.deadlines.splice(id, 1);
            });
        }
    }
</script>
