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

        <input type="hidden" name="grading_method" :value="form.fields.grading_method">
        <input type="hidden" name="max_score" :value="form.fields.max_score">
        <input type="hidden" name="calculation_formula" :value="form.fields.calculation_formula">
        <div v-for="grademap in form.fields.grademaps">
            <input type="hidden"
               :name="'grademaps[' + grademap.grade_type_code + '][grademap_name]'"
               :value="grademap.name">
            <input type="hidden"
                   :name="'grademaps[' + grademap.grade_type_code + '][max_points]'"
                   :value="grademap.max_points">
            <input type="hidden"
                   :name="'grademaps[' + grademap.grade_type_code + '][id_number]'"
                   :value="grademap.id_number">
        </div>

    </div>
</template>

<script>
    import { CharonSelect } from '../../../components/form';
    import { Translate, EmitEventOnInputChange } from '../../../mixins';

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
