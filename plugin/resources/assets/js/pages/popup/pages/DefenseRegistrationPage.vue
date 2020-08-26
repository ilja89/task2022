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
                this.defenseList = response
            })
        },
        methods: {
            apply(after, before) {
                Defense.filtered(this.course.id, after, before, response => {
                    this.defenseList = response
                })
            },

        }
    }
</script>
