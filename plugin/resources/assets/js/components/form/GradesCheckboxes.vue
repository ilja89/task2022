<template>
    <div class="fcontainer clearfix">
        <div class="fitem fitem_fcheck">
            <div class="fitemtitle">
                <label>{{ label }}</label>
            </div>
            <div class="felement fcheck grades-select-container">
                <div class="grades-select-col">
                    <label class="checkbox" v-for="grade_type in testGradeTypes()">
                        <input type="checkbox" @click="toggleClicked(grade_type.code)"
                               :checked="isActive(grade_type.code)">
                        {{ grade_type.name }}
                    </label>
                </div>
                <div class="grades-select-col">
                    <label class="checkbox" v-for="grade_type in styleGradeTypes()">
                        <input type="checkbox" @click="toggleClicked(grade_type.code)"
                               :checked="isActive(grade_type.code)">
                        {{ grade_type.name }}
                    </label>
                </div>
                <div class="grades-select-col">
                    <label class="checkbox" v-for="grade_type in customGradeTypes()">
                        <input type="checkbox" @click="toggleClicked(grade_type.code)"
                               :checked="isActive(grade_type.code)">
                        {{ grade_type.name }}
                    </label>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: [ 'label', 'active_grade_type_codes' ],

        data() {
            return {
                active: this.active_grade_type_codes
            }
        },

        watch: {
            active_grade_type_codes() {
                this.active = this.active_grade_type_codes;
            }
        },

        methods: {
            toggleClicked(code) {
                if (this.isActive(code)) {
                    const index = this.active.indexOf(code);
                    if (index !== -1) {
                        this.active.splice(index, 1);
                    }

                    this.$emit('grade-type-was-deactivated', code);
                } else {
                    this.active.push(code);

                    this.$emit('grade-type-was-activated', code);
                }
            },

            isActive(code) {
                return this.active.includes(code);
            },

            testGradeTypes() {

                let activeTestGrades = this.active.filter(grade_type_code => {
                    return grade_type_code <= 100;
                });

                activeTestGrades = activeTestGrades.map(grade_type_code => {
                    return {
                        code: grade_type_code,
                        name: this.getGradeTypeName(grade_type_code),
                    }
                });
                if (activeTestGrades.length > 0) {
                    activeTestGrades.push({
                        code: activeTestGrades[ activeTestGrades.length - 1 ].code + 1,
                        name: this.getGradeTypeName(activeTestGrades[ activeTestGrades.length - 1 ].code + 1)
                    })
                } else {
                    activeTestGrades.push({
                        code: 1,
                        name: this.getGradeTypeName(1)
                    })
                    activeTestGrades.push({
                        code: 2,
                        name: this.getGradeTypeName(2)
                    })
                }

                return activeTestGrades;
            },

            styleGradeTypes() {
                let activeStyleGrades = this.active.filter(grade_type_code => {
                    return grade_type_code <= 1000 && grade_type_code > 100;
                });

                activeStyleGrades = activeStyleGrades.map(grade_type_code => {
                    return {
                        code: grade_type_code,
                        name: this.getGradeTypeName(grade_type_code),
                    }
                });
                if (activeStyleGrades.length > 0) {
                    activeStyleGrades.push({
                        code: activeStyleGrades[ activeStyleGrades.length - 1 ].code + 1,
                        name: this.getGradeTypeName(activeStyleGrades[ activeStyleGrades.length - 1 ].code + 1)
                    });
                } else {
                    activeStyleGrades.push({
                        code: 101,
                        name: this.getGradeTypeName(101)
                    })
                    activeStyleGrades.push({
                        code: 102,
                        name: this.getGradeTypeName(102)
                    })
                }

                return activeStyleGrades;
            },

            customGradeTypes() {
                let activeCustomGrades = this.active.filter(grade_type_code => {
                    return grade_type_code > 1000;
                });

                activeCustomGrades = activeCustomGrades.map(grade_type_code => {
                    return {
                        code: grade_type_code,
                        name: this.getGradeTypeName(grade_type_code),
                    }
                });
                if (activeCustomGrades.length > 0) {
                    activeCustomGrades.push({
                        code: activeCustomGrades[ activeCustomGrades.length - 1 ].code + 1,
                        name: this.getGradeTypeName(activeCustomGrades[ activeCustomGrades.length - 1 ].code + 1)
                    });
                } else {
                    activeCustomGrades.push({
                        code: 1001,
                        name: this.getGradeTypeName(1001)
                    })
                    activeCustomGrades.push({
                        code: 1002,
                        name: this.getGradeTypeName(1002)
                    })
                }

                return activeCustomGrades;
            },

            getGradeTypeName(grade_type_code) {
                // TODO: Refactor to mixin?
                let gradeTypeName = '';
                if (grade_type_code <= 100) {
                    gradeTypeName = 'Tests_' + grade_type_code;
                } else if (grade_type_code <= 1000) {
                    gradeTypeName = 'Style_' + grade_type_code % 100;
                } else {
                    gradeTypeName = 'Custom_' + grade_type_code % 1000;
                }

                return gradeTypeName;
            },
        },
    }
</script>
