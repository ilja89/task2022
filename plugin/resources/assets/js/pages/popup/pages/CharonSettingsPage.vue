<template>
    <div>
        <v-card class="mb-16 pl-4">
            <v-card-title>Charon settings</v-card-title>
        </v-card>

        <charon-settings-section></charon-settings-section>

        <tester-type-section :testerTypes="testerTypes" :course-id="this.course.id"></tester-type-section>
    </div>
</template>

<script>
    import CharonSettingsSection from "../sections/CharonSettingsSection";
    import TesterTypeSection from "../sections/TesterTypeSection";
    import Charon from "../../../api/Charon";
    import Course from "../../../api/Course";
    import {mapState} from "vuex";

    export default {
        name: "defense-settings-page",
        components: {CharonSettingsSection, TesterTypeSection},
        data() {
            return {
                testerTypes: []
            }
        },
        computed: {
            ...mapState([
                'course'
            ]),
        },

        created() {
            Course.getTesterTypes(this.course.id, response => {
                this.testerTypes = response
            })
        }
    }
</script>
