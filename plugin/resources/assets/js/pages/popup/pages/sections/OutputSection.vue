<template>

    <popup-section
            title="Email and outputs"
            subtitle="Output from the tester and mail sent to the student."
            class="output-section">

        <charon-tabs class="card" v-if="submission !== null">

            <charon-tab name="Code" :selected="true">

                <files-component
                        :submission="submission"
                        :testerType="charon.tester_type_name"
                        :isRound="false">
                </files-component>

            </charon-tab>

            <charon-tab name="Mail">

                <pre class="output-content" v-if="hasMail">{{ submission.mail }}</pre>

            </charon-tab>

            <charon-tab name="Outputs">

                <output-component
                        :submission="submission"
                        :grademaps="charon.grademaps">
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

        computed: {
            hasMail() {
                return typeof this.submission.mail !== 'undefined' && this.submission.mail !== null && this.submission.mail.length > 0;
            }
        }
    }
</script>
