<template>

    <popup-section
            title="Email and outputs"
            subtitle="Output from the tester and mail sent to the student."
            class="output-section">

        <charon-tabs class="card" v-if="submission !== null">

            <charon-tab name="Code" :selected="true">

                <files-component
                        :submission="submission"
                        :testerType="charon.tester_type_name">
                </files-component>

            </charon-tab>

            <charon-tab name="Mail">

                <pre class="output-content">{{ submission.mail }}</pre>

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
    import PopupSection from '../../layouts/PopupSection.vue';
    import CharonTabs from '../../../components/partials/CharonTabs.vue';
    import CharonTab from '../../../components/partials/CharonTab.vue';
    import FilesComponent from '../../components/FilesComponent.vue';
    import OutputComponent from '../../components/OutputComponent.vue';

    export default {

        components: { PopupSection, CharonTabs, CharonTab, FilesComponent, OutputComponent },

        props: {
            submission: { required: true },
            charon: { required: true }
        },
    }
</script>
