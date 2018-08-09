<template>
    <div>
        <instance-form-fieldset
            toggle_id="tgl1"
            @advanced-was-toggled="toggleAdvancedInfoSection">

            <template slot="title">{{ translate('task_info_title') }}</template>

            <slot>
                <advanced-task-info-section
                    v-if="advanced_info_section_active"
                    :form="form">
                </advanced-task-info-section>
                <simple-task-info-section
                    v-else
                    :form="form">
                </simple-task-info-section>
            </slot>

        </instance-form-fieldset>

        <instance-form-fieldset
                toggle_id="tgl2"
                @advanced-was-toggled="toggleAdvancedPlagiarismSection">

            <template slot="title">{{ translate('plagiarism_detection') }}</template>

            <slot>
                <advanced-plagiarism-section
                        v-if="advanced_plagiarism_section_active"
                        :form="form">
                </advanced-plagiarism-section>
                <advanced-plagiarism-section
                    v-else
                    :form="form">
                </advanced-plagiarism-section>
                <!--<simple-plagiarism-section-->
                        <!--v-else-->
                        <!--:form="form">-->
                <!--</simple-plagiarism-section>-->
            </slot>

        </instance-form-fieldset>

        <instance-form-fieldset
                toggle_id="tgl3"
                @advanced-was-toggled="toggleAdvancedGradingSection">

            <template slot="title">{{ translate('grading_title') }}</template>

            <slot>
                <advanced-grading-section
                        v-if="advanced_grading_section_active"
                        :form="form">
                </advanced-grading-section>
                <simple-grading-section
                        v-else
                        :form="form">
                </simple-grading-section>
            </slot>

        </instance-form-fieldset>

        <deadline-section :form="form"></deadline-section>

        <notification :text="notification.text" :show="notification.show" :type="notification.type">
        </notification>
    </div>
</template>

<script>
    import {
        AdvancedTaskInfoSection, AdvancedGradingSection, SimpleTaskInfoSection,
        SimpleGradingSection, DeadlineSection, AdvancedPlagiarismSection,
        SimplePlagiarismSection,
    } from './sections'
    import { InstanceFormFieldset } from '../../components/form'
    import { Translate } from '../../mixins'
    import { Notification } from '../../components/partials'

    export default {
        mixins: [ Translate ],

        props: {
            form: { required: true }
        },

        components: {
            SimpleTaskInfoSection, SimpleGradingSection, DeadlineSection,
            AdvancedTaskInfoSection, AdvancedGradingSection,
            InstanceFormFieldset, Notification, AdvancedPlagiarismSection,
            SimplePlagiarismSection,
        },

        data() {
            return {
                advanced_info_section_active: false,
                advanced_grading_section_active: false,
                advanced_plagiarism_section_active: false,

                notification: {
                    text: '',
                    show: false,
                    type: 'success',
                }
            }
        },

        methods: {
            toggleAdvancedInfoSection(advanced_toggle) {
                this.advanced_info_section_active = advanced_toggle;
            },

            toggleAdvancedGradingSection(advanced_toggle) {
                this.advanced_grading_section_active = advanced_toggle;
            },

            toggleAdvancedPlagiarismSection(advanced_toggle) {
                this.advanced_plagiarism_section_active = advanced_toggle;
            },

            showNotification(message, type, timeout = 5000) {
                this.notification.text = message
                this.notification.show = true
                this.notification.type = type

                if (timeout) {
                    setTimeout(() => {
                        this.notification.show = false
                    }, timeout)
                }
            },

            hideNotification() {
                this.notification.show = false
            },
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

            VueEvent.$on('calculation-formula-was-changed', (calculationFormula) => this.form.fields.calculation_formula = calculationFormula);
            VueEvent.$on('max-score-was-changed', (maxScore) => this.form.fields.max_score = maxScore);

            VueEvent.$on('preset-was-changed', presetId => this.form.onPresetSelected(presetId));

            VueEvent.$on('show-notification', (message, type = 'success', timeout = 5000) => {
                this.showNotification(message, type, timeout)
            })
            VueEvent.$on('close-notification', () => {
                this.hideNotification()
            })

            VueEvent.$on('plagiarism-service-was-changed', (index, serviceCode) => {
                this.form.fields.plagiarism_services[index] = serviceCode
            })
            VueEvent.$on('plagiarism-service-was-added', () => {
                this.form.fields.plagiarism_services.push(null)
            })
            VueEvent.$on('plagiarism-service-was-removed', index => {
                this.form.fields.plagiarism_services.splice(index, 1)
            })
            VueEvent.$on('plagiarism-enabled-was-changed', (plagiarismEnabled) => {
                this.form.fields.plagiarism_enabled = plagiarismEnabled
            })
            VueEvent.$on('plagiarism-resource-provider-was-added', () => {
                this.form.fields.plagiarism_resource_providers.push({
                    repository: '',
                })
            })
            VueEvent.$on('plagiarism-resource-provider-repository-changed', (index, repo) => {
                this.form.fields.plagiarism_resource_providers[index].repository = repo
            })
            VueEvent.$on('plagiarism-resource-provider-removed', index => {
                this.form.fields.plagiarism_resource_providers.splice(index, 1)
            })
            VueEvent.$on('plagiarism-excludes-was-changed', excludes => {
                this.form.fields.plagiarism_excludes = excludes
            })
        },
    }
</script>
