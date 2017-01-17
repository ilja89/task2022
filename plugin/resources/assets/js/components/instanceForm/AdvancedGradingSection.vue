<template>
    <div>
        <charon-select
                :label="translate('grading_method_label')"
                name="grading_method"
                :options="form.grading_methods"
                :selected="form.fields.grading_method"
                @input-was-changed="onGradingMethodChanged" >
        </charon-select>

        <grades-checkboxes
                :label="translate('grademaps_label')"
                :grade_types="form.grade_types"
                :active_grade_type_codes="getActiveGradeTypes()"
                @grade-type-was-activated="onGradeTypeActivated"
                @grade-type-was-deactivated="onGradeTypeDeactivated">
        </grades-checkboxes>
    </div>
</template>

<script>
    import CharonSelect from '../form/CharonSelect.vue';
    import GradesCheckboxes from '../form/GradesCheckboxes.vue';

    import Translate from '../../mixins/translate';
    import EmitEventOnInputChange from '../../mixins/emitEventOnInputChange';

    export default {
        mixins: [ Translate, EmitEventOnInputChange ],

        components: { CharonSelect, GradesCheckboxes },

        props: [ 'form' ],

        methods: {
            getActiveGradeTypes() {
                let active_grademaps = [];

                this.form.fields.grademaps.forEach((grademap) => {
                    active_grademaps.push(grademap.grade_type_code);
                });

                return active_grademaps;
            }
        }
    }
</script>
