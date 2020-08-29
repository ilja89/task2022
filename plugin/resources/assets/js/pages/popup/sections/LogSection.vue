<template>
    <popup-section
            title="Charon logs"
            subtitle="Here are the recent errors charon has had"
    >
        <div style="height:600px;overflow:auto">
            <pre>
                {{logs}}
            </pre>
        </div>

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
