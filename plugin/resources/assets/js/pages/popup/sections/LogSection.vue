<template>
    <popup-section title="Charon logs"
                   subtitle="Here are the recent errors charon has had">

        <v-card class="mx-auto" max-height="900" max-width="90vw" outlined raised>
            <pre style="max-height: 900px;overflow: auto">
                {{logs}}
            </pre>

        </v-card>

    </popup-section>
</template>

<script>
    import {mapGetters} from 'vuex'
    import {Charon, Submission} from '../../../api/index'
    import {PopupSection} from '../layouts/index'

    export default {
        name: 'log-section',

        components: {PopupSection},

        data() {
            return {
                logs: "",
            }
        },

        computed: {
            ...mapGetters([
                'courseId',
            ]),
        },

        mounted() {
            this.fetchLogs()
            VueEvent.$on('refresh-page', this.fetchLogs);
        },

        /**
         * Remove global event listeners for more efficient refreshes on other
         * pages.
         */
        deactivated() {
            VueEvent.$off('refresh-page', this.fetchLogs)
        },

        methods: {
            fetchLogs() {
                Charon.fetchLatestLogs(this.courseId, counts => {
                    this.logs = counts
                })
            },
        },
    }
</script>
