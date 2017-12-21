<template>
    <popup-section
            title="Email and outputs"
            subtitle="Output from the tester and mail sent to the student."
            class="output-section"
    >

        <charon-tabs
            v-if="submission"
            class="card"
            id="tabs"
            :sticky="stickyTabs"
        >

            <charon-tab name="Code" :selected="true">

                <files-component
                        :submission="submission"
                        :testerType="charon ? charon.tester_type_name : ''"
                        :isRound="false"
                />

            </charon-tab>

            <charon-tab name="Mail">

                <pre class="output-content" v-if="hasMail">{{ submission.mail }}</pre>

            </charon-tab>

            <charon-tab name="Outputs">

                <output-component :grademaps="charon ? charon.grademaps : []"/>

            </charon-tab>

        </charon-tabs>

    </popup-section>
</template>

<script>
    import { mapState } from 'vuex'
    import { CharonTabs, CharonTab, FilesComponent } from '../../../components/partials/index';
    import { PopupSection } from '../layouts/index';
    import { OutputComponent } from '../partials/index';

    export default {

        components: { PopupSection, CharonTabs, CharonTab, FilesComponent, OutputComponent },

        data() {
            return {
                stickyTabs: false,
            }
        },

        computed: {
            ...mapState([
                'charon',
                'submission',
            ]),

            hasMail() {
                return typeof this.submission.mail !== 'undefined' && this.submission.mail !== null && this.submission.mail.length > 0;
            },
        },

        watch: {
            submission() {
                this.registerScrollListener()
            },
        },

        methods: {
            registerScrollListener() {
                document.getElementById('page-right').removeEventListener('scroll', this.scrollHandler)
                document.getElementById('page-right').addEventListener('scroll', this.scrollHandler)
            },

            scrollHandler(e) {
                if (!document.getElementById('page-right') || !document.getElementById('tabs')) return

                if (this.stickyTabs) {
                    if (e.target.scrollTop + document.getElementById('page-right').offsetTop < document.getElementById('tabs').offsetTop) {
                        this.stickyTabs = false
                    }
                } else {
                    if (e.target.scrollTop + document.getElementById('page-right').offsetTop > document.getElementById('tabs').offsetTop) {
                        this.stickyTabs = true
                    }
                }
            },
        },

        mounted() {
            this.registerScrollListener()
        },
    }
</script>
