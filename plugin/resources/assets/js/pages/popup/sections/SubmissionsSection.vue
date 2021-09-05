<template>
    <popup-section
        title="Pick a task"
        subtitle="Here are the submissions from one student for the given task."
    >

        <template>
            <v-spacer></v-spacer>
            Total points: {{ totalCharonPoints }}
            <v-spacer></v-spacer>
            <charon-select/>
        </template>

        <submissions-list/>

    </popup-section>
</template>

<script>
    import { PopupSection } from '../layouts';
    import { CharonSelect, SubmissionsList } from '../partials';
    import {mapState} from "vuex";
    import {Charon} from "../../../api";

    export default {
        components: { PopupSection, CharonSelect, SubmissionsList },

        data() {
            return {
                totalCharonPoints: null
            }
        },

        computed: {
            ...mapState([
                'charon',
                'student',
            ]),
        },

        methods: {

            refreshPoints() {
                if (this.student == null || this.charon == null || this._inactive) {
                    return
                }

                Charon.getResultForStudent(
                    this.charon.id,
                    this.student.id,
                    points => {
                        this.totalCharonPoints = points ? points : 0;
                    }
                );
            },
        },

        watch: {
            charon() {
                this.refreshPoints()
            },

            student() {
                this.refreshPoints()
            },
        },

        created() {
            this.refreshPoints()
            VueEvent.$on('refresh-page', this.refreshPoints)
        },

        beforeDestroy() {
            VueEvent.$off('refresh-page', this.refreshPoints)
        },
    }
</script>
