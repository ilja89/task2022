<template>
    <popup-section
            title="Email and outputs"
            subtitle="Output from the tester and mail sent to the student."
            class="output-section">

        <charon-tabs :sticky="stickyTabs" id="tabs" class="card" v-if="submission !== null">

            <charon-tab name="Code" :selected="true">

                <files-component
                        :submission="submission"
                        :testerType="charon ? charon.tester_type_name : ''"
                        :isRound="false">
                </files-component>

            </charon-tab>

            <charon-tab name="Mail">

                <pre class="output-content" v-if="hasMail">{{ submission.mail }}</pre>

            </charon-tab>

            <charon-tab name="Outputs">

                <output-component
                        :submission="submission"
                        :grademaps="charon ? charon.grademaps : []">
                </output-component>

            </charon-tab>

        </charon-tabs>

    </popup-section>
</template>

<script>
    import { CharonTabs, CharonTab, FilesComponent } from '../../../../components/partials';
    import { PopupSection } from '../../layouts';
    import { OutputComponent } from '../../components';

    export default {

        components: { PopupSection, CharonTabs, CharonTab, FilesComponent, OutputComponent },

        props: {
            submission: { required: true },
            charon: { required: true }
        },

        data() {
            return {
                stickyTabs: false,
            }
        },

        computed: {
            hasMail() {
                return typeof this.submission.mail !== 'undefined' && this.submission.mail !== null && this.submission.mail.length > 0;
            }
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
