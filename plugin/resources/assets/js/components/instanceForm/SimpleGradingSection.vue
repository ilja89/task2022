<template>
    <div>

        <charon-select v-if="!isUpdate"
                       :label="translate('preset_label')"
                       name="preset"
                       :options="form.presets"
                       :selected="form.fields.preset !== null ? form.fields.preset.id : null"
                       key_field="id"
                       @input-was-changed="onPresetChanged">
        </charon-select>

        <div class="simple-grading-info">

            <p>
                Total points: {{ form.fields.max_score }}
            </p>

            <p>
                Grades:
            </p>

            <ul>
                <li v-for="grademap in form.fields.grademaps">
                    {{ getGradeTypeName(grademap.grade_type_code) }}: {{ grademap.name }}, {{ grademap.max_points }}p
                </li>
            </ul>

            <p>
                Total grade calculation formula: {{ form.fields.calculation_formula }}
            </p>

        </div>

    </div>
</template>

<script>
    import CharonSelect from '../form/CharonSelect.vue';

    import Translate from '../../mixins/translate';
    import EmitEventOnInputChange from '../../mixins/emitEventOnInputChange';

    export default {
        mixins: [ Translate, EmitEventOnInputChange ],

        components: { CharonSelect },

        props: {
            form: { required: true }
        },

        computed: {
            isUpdate() {
                return window.isEditing;
            }
        },

        methods: {
            getGradeTypeName(grade_type_code) {
                let grade_name = '';

                this.form.grade_types.forEach((grade_type) => {
                    if (grade_type.code === grade_type_code) {
                        grade_name = grade_type.name;
                    }
                });

                return grade_name;
            },
        }

    }
</script>
