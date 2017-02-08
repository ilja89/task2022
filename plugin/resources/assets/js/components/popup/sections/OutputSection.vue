<template>

    <popup-section
            title="Email and outputs"
            subtitle="Output from the tester and mail sent to the student."
            class="output-section">

        <charon-tabs class="card">

            <charon-tab name="Code" :selected="true">

                <p class="control">
                    <span class="select">
                        <select name="file" v-if="context.active_submission !== null"
                                @change="onFileChanged"
                                v-model="active_file_id">
                            <option v-for="file in context.active_submission.files"
                                    :value="file.id">
                                {{ file.path }}
                            </option>
                        </select>
                    </span>
                </p>

                <pre v-if="context.active_file !== null">
                    <code :class="context.active_charon.tester_type_name">{{ context.active_file.contents }}</code>
                </pre>

            </charon-tab>

            <charon-tab name="Mail">

                <pre v-if="context.active_submission !== null" class="output-content">{{ context.active_submission.mail }}</pre>

            </charon-tab>

            <charon-tab name="Outputs">

                <p class="control">
                    <span class="select">

                        <select name="output" v-if="context.active_submission !== null"
                                v-model="active_output_slug">

                            <option v-if="context.active_submission.stdout !== null"
                                    value="submission__stdout">Submission stdout</option>
                            <option v-if="context.active_submission.stderr !== null"
                                    value="submission__stderr">Submission stderr</option>

                            <option v-for="result in context.active_submission.results"
                                    :value="'result__stdout__' + result.id"
                                    v-if="result.stdout !== null">
                                {{ getGrademapByResult(result).name }} stdout
                            </option>

                            <option v-for="result in context.active_submission.results"
                                    :value="'result__stderr__' + result.id"
                                    v-if="result.stderr !== null">
                                {{ getGrademapByResult(result).name }} stderr
                            </option>

                        </select>

                    </span>
                </p>

                <pre class="output-content">{{ selectedOutput }}</pre>

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
                active_file_id: null,
                active_output_slug: 'submission__stdout'
            };
        },

        computed: {

            selectedOutput() {

                if (this.active_output_slug === null) {
                    return 'No output selected.';
                }

                let slug = this.active_output_slug.split('__');
                let outputFrom = this.context.active_submission;
                if (slug[0] == 'result') {
                    outputFrom = this.findResultById(slug[2]);
                }

                if (outputFrom === null) {
                    return '';
                }

                return outputFrom[slug[1]];
            }
        },

        mounted() {
            if (this.context.active_submission !== null) {
                this.active_file_id = this.context.active_submission.files[0];
            }
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
            },

            getGrademapByResult(result) {
                let correctGrademap = null;
                this.context.active_charon.grademaps.forEach((grademap) => {
                    if (result.grade_type_code == grademap.grade_type_code) {
                        correctGrademap = grademap;
                    }
                });

                return correctGrademap;
            },

            findResultById(id) {
                let matchingResult = null;

                this.context.active_submission.results.forEach((result) => {
                    if (result.id == id) {
                        matchingResult = result;
                    }
                });

                return matchingResult;
            },
        }
    }
</script>
