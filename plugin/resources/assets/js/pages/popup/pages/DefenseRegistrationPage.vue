<template>
    <div>
        <page-title :title="'Defense registrations'"></page-title>
        <defense-registrations-section :defense-list="defenseList" :apply="apply"></defense-registrations-section>
    </div>
</template>

<script>

    import PageTitle from "../partials/PageTitle";
    import DefenseRegistrationsSection from "../sections/DefenseRegistrationsSection";
    import {mapState} from "vuex";
    import Defense from "../../../api/Defense";

    export default {
        name: "defense-registrations-page",
        components: { PageTitle, DefenseRegistrationsSection },
        data() {
            return {
                defenseList: [],
                countDown: 0
            }
        },
        computed: {

            ...mapState([
                'course'
            ]),
        },
        mounted() {
            Defense.all(this.course.id, response => {
                this.getTeachersForStudents(response, result => {
                    this.defenseList = response
                })
            })
        },
        methods: {
            apply(after, before) {
                Defense.filtered(this.course.id, after, before, response => {
                    this.getTeachersForStudents(response, result => {
                        this.defenseList = result
                    })
                })
            },
            getTeachersForStudents(defenses, then) {
                this.countDown = defenses.length;
                for (let i = 0; i < defenses.length; i++) {
                    if (defenses[i].my_teacher) {
                        Defense.getTeacherForStudent(this.course.id, defenses[i].student_id, response => {
                            defenses[i].teacher = response[0]
                            this.countDown--
                            if (!this.countDown) {
                                then(defenses)
                            }
                        })
                    } else {
                        defenses[i].teacher = {firstname: 'Any', lastname: ''}
                        this.countDown--
                        if (!this.countDown) {
                            then(defenses)
                        }
                    }
                }
            }
        }
    }
</script>
