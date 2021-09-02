<template>
    <popup-section
            title="Email and outputs"
            subtitle="Output from the tester and mail sent to the student."
            class="output-section"
    >
        <charon-tabs
                v-if="submission"
                class="card popup-tabs"
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

                <v-card class="mx-auto" max-height="900" max-width="80vw" outlined raised v-if="hasMail">
                    <pre style="max-height: 900px;overflow: auto" v-html="submission.mail"/>
                </v-card>

            </charon-tab>

            <charon-tab name="Comments">

                <comment-component :files="submission.files"/>

            </charon-tab>

            <charon-tab name="Outputs">

                <output-component :submission="submission"/>

            </charon-tab>

        </charon-tabs>

    </popup-section>
</template>

<script>
    import {mapState} from 'vuex'
    import {CharonTabs, CharonTab, FilesComponent, CommentComponent} from '../../../components/partials/index';
    import {PopupSection} from '../layouts/index';
    import {OutputComponent} from '../partials/index';

    export default {

        components: {PopupSection, CharonTabs, CharonTab, FilesComponent, OutputComponent, CommentComponent},

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
        }
    }
</script>
