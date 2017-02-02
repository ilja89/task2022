<template>

    <popup-section
            title="Email and outputs"
            subtitle="Output from the tester and mail sent to the student."
            class="output-section">

        <charon-tabs class="card">

            <charon-tab name="Code" :selected="true">

                <select name="file" v-if="context.active_submission !== null"
                        @change="onFileChanged"
                        v-model="active_file_id">
                    <option v-for="file in context.active_submission.files"
                            :value="file.id">
                        {{ file.path }}
                    </option>
                </select>

                <pre v-if="context.active_file !== null">
                    <code :class="context.active_charon.tester_type_name">{{ context.active_file.contents }}</code>
                </pre>

            </charon-tab>

            <charon-tab name="Mail">
                Hello Mail!
            </charon-tab>

            <charon-tab name="Outputs">
                Hello Outputs!
            </charon-tab>

        </charon-tabs>

    </popup-section>
</template>

<script>
    import PopupSection from '../partials/PopupSection.vue';
    import CharonTabs from '../../partials/CharonTabs.vue';
    import CharonTab from '../../partials/CharonTab.vue';

    export default {
        components: { PopupSection, CharonTabs, CharonTab },

        props: {
            context: { required: true }
        },

        data() {
            return {
                active_file_id: null
            };
        },

        methods: {
            onFileChanged() {

                let changedFile = null;

                this.context.active_submission.files.forEach((file) => {
                    if (file.id == this.active_file_id) {
                        changedFile = file;
                    }
                });

                VueEvent.$emit('file-was-changed', changedFile);
            }
        }
    }
</script>
