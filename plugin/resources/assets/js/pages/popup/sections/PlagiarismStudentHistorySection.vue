<template>
    <popup-section
        title="Student plagiarism history"
        subtitle="This section displays information about the student's plagiarism history across multiple Charons"
    >

        <template slot="header-right">
            <v-btn class="ma-2" tile outlined color="primary" @click="fetchStudentMatches()">{{ showStudentHistoryText }}</v-btn>
        </template>

    </popup-section>
</template>

<script>
import {PopupSection} from '../layouts';
import {Plagiarism} from "../../../api";
import {mapGetters} from "vuex";

export default {
    components: {PopupSection},
    props: ['student'],

    data() {
        return {
            matches: []
        }
    },

    computed: {
        ...mapGetters([
            'courseId',
        ]),

        showStudentHistoryText() {
            return 'Fetch student matches'
        }
    },

    methods: {
        fetchStudentMatches() {
            Plagiarism.fetchStudentMatches(this.courseId, this.student.username, (response) => {
                this.matches = response
            })
        }
    }
}
</script>

<style scoped>

</style>