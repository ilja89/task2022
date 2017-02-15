<template>
    <div class="fcontainer clearfix">
        <div class="fitem fitem_fcheck">
            <div class="fitemtitle">
                <label>{{ label }}</label>
            </div>
            <div class="felement fcheck grades-select-container">
                <div class="grades-select-col">
                    <label class="checkbox" v-for="grade_type in testGradeTypes()">
                        <input type="checkbox" @click="toggleClicked(grade_type.code)" :checked="isActive(grade_type.code)">
                        {{ grade_type.name }}
                    </label>
                </div>
                <div class="grades-select-col">
                    <label class="checkbox" v-for="grade_type in styleGradeTypes()">
                        <input type="checkbox" @click="toggleClicked(grade_type.code)" :checked="isActive(grade_type.code)">
                        {{ grade_type.name }}
                    </label>
                </div>
                <div class="grades-select-col">
                    <label class="checkbox" v-for="grade_type in customGradeTypes()">
                        <input type="checkbox" @click="toggleClicked(grade_type.code)" :checked="isActive(grade_type.code)">
                        {{ grade_type.name }}
                    </label>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: [ 'label', 'grade_types', 'active_grade_type_codes' ],

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
                return this.grade_types.filter((grade_type) => {
                    return grade_type.code < 100;
                });
            },

            styleGradeTypes() {
                return this.grade_types.filter((grade_type) => {
                    return grade_type.code >= 100 && grade_type.code < 1000;
                });
            },

            customGradeTypes() {
                return this.grade_types.filter((grade_type) => {
                    return grade_type.code >= 1000;
                });
            }
        },
    }
</script>
