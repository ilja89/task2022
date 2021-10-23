<template>
    <div>
        <v-card class="mb-16 pl-4">
            <v-card-title>Charon settings</v-card-title>
        </v-card>

        <charon-settings-section></charon-settings-section>

        <tester-type-section :course-id="this.course.id"></tester-type-section>
    </div>
</template>

<script>
    import CharonSettingsSection from "../sections/CharonSettingsSection";
    import TesterTypeSection from "../sections/TesterTypeSection";
    import {mapState} from "vuex";

    export default {
        name: "defense-settings-page",
        components: {CharonSettingsSection, TesterTypeSection},
        computed: {
            ...mapState([
                'course'
            ]),
        },

        metaInfo() {
            return {
                title: `${'Charon settings - ' + window.course_name}`
            }
        },
    }
</script>
