<template>
    <div>

        <grades-checkboxes
                :label="translate('grades_label')"
                :grade_types="gradeTypes"
                :active_grade_type_codes="activeGradeTypeCodes"
                @grade-type-was-activated="addGrade"
                @grade-type-was-deactivated="removeGrade">
        </grades-checkboxes>

        <charon-tabs v-if="grades.length > 0">

            <charon-tab
                    v-for="(grade, index) in grades"
                    :name="getGradeTypeName(grade.grade_type_code)"
                    :selected="index === 0 ? true : false">

                <h5 class="subtitle">{{ translate('grade_name_label') }}</h5>

                <div class="grade-name-container">

                    <div>
                        <label :for="'preset_grade_name_prefix_' + index">
                            {{ translate('grade_name_prefix_label') }}
                        </label>
                        <br>

                        <select v-model="grade.grade_name_prefix_code"
                                :id="'preset_grade_name_prefix_' + index">
                            <option
                                    v-for="grade_name_prefix in gradeNamePrefixes"
                                    :value="grade_name_prefix.code">
                                {{ grade_name_prefix.name }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label :for="'preset_grade_name_' + index">
                            {{ translate('grade_name_postfix_label') }}
                        </label>

                        <input type="text" class="form-control" v-model="grade.grade_name"
                               :id="'preset_grade_name_' + index">
                    </div>

                </div>

                <div class="max-points-container">

                    <label :for="'preset_grade_max_points_' + index">
                        {{ translate('max_points_label') }}
                    </label>

                    <input type="number" class="form-control" v-model="grade.max_result"
                           :id="'preset_grade_max_points_' + index" step="0.01">
                </div>

                <div class="id-number-postfix-container">

                    <label :for="'preset_grade_id_number_postfix_' + index">
                        {{ translate('id_number_postfix_label') }}
                    </label>

                    <input type="text" class="form-control" v-model="grade.id_number_postfix"
                           :id="'preset_grade_id_number_postfix_' + index">

                </div>

            </charon-tab>

        </charon-tabs>

    </div>
</template>

<script>
    import { GradesCheckboxes } from '../../../components/form';
    import { CharonTabs, CharonTab } from '../../../components/partials';
    import { Translate } from '../../../mixins';

    export default {

        mixins: [ Translate ],

        components: { GradesCheckboxes, CharonTabs, CharonTab },

        props: {
            gradeTypes: { required: true },
            grades: { required: true },
            gradeNamePrefixes: { required: true },
        },

        computed: {
            activeGradeTypeCodes() {
                let activeGradeCodes = [];
                this.grades.forEach(grade => {
                    activeGradeCodes.push(grade.grade_type_code);
                });
                return activeGradeCodes;
            },
        },

        methods: {

            addGrade(gradeTypeCode) {
                let grade = {
                    grade_name_prefix_code: this.gradeNamePrefixes.length > 0 ? this.gradeNamePrefixes[0].code : null,
                    grade_type_code: gradeTypeCode,
                    grade_name: null,
                    max_result: null,
                    id_number_postfix: null,
                };
                this.grades.push(grade);

                this.grades.sort((a, b) => {
                    return a.grade_type_code > b.grade_type_code ? 1 : -1;
                });
            },

            removeGrade(gradeTypeCode) {
                let removedIndex = -1;

                this.grades.forEach((grade, index) => {
                    if (gradeTypeCode == grade.grade_type_code) {
                        removedIndex = index;
                    }
                });

                this.grades.splice(removedIndex, 1);
            },

            getGradeTypeName(grade_type_code) {
                let grade_name = '';

                this.gradeTypes.forEach((grade_type) => {
                    if (grade_type.code === grade_type_code) {
                        grade_name = grade_type.name;
                    }
                });

                return grade_name;
            },
        }
    }
</script>
