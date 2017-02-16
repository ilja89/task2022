<template>

    <popup-section
            title="Email and outputs"
            subtitle="Output from the tester and mail sent to the student."
            class="output-section">

        <charon-tabs class="card" v-if="submission !== null">

            <charon-tab name="Code" :selected="true">

                <p class="control" v-if="submission.files.length > 0">
                    <span class="select">
                        <select name="file"
                                @change="onFileChanged"
                                v-model="active_file_id">
                            <option v-for="file in submission.files"
                                    :value="file.id">
                                {{ file.path }}
                            </option>
                        </select>
                    </span>
                </p>

                <pre v-if="activeFile !== null">
                    <code :class="charon.tester_type_name">{{ activeFile.contents }}</code>
                </pre>

            </charon-tab>

            <charon-tab name="Mail">

                <pre class="output-content">{{ submission.mail }}</pre>

            </charon-tab>

            <charon-tab name="Outputs">

                <p class="control">
                    <span class="select">

                        <select name="output"
                                v-model="active_output_slug">

                            <option v-for="output in getOutputs()"
                                    :value="output.value">
                                {{ output.title }}
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
            submission: { required: true },
            charon: { required: true }
        },

        data() {
            return {
                active_file_id: null,
                active_output_slug: '',
                activeFile: null
            };
        },

        computed: {

            selectedOutput() {

                if (this.active_output_slug === null) {
                    return 'No output selected.';
                }

                let slug = this.active_output_slug.split('__');
                let outputFrom = this.submission;
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
            if (this.submission !== null) {
                this.active_file_id = this.submission.files[0].id;
            }
        },

        watch: {
            active_file_id() {
                this.submission.files.forEach(file => {
                    if (file.id === this.active_file_id) {
                        this.activeFile = file;
                    }
                });
            },

            submission() {
                let outputs = this.getOutputs();
                if (outputs.length > 0) {
                    this.active_output_slug = outputs[0].value;
                }

                if (this.submission === null || this.submission.files.length === 0) {
                    this.active_file_id = null;
                    return;
                }

                this.active_file_id = this.submission.files[0].id;
            }
        },

        methods: {
            onFileChanged() {

                let changedFile = null;

                this.submission.files.forEach((file) => {
                    if (file.id == this.active_file_id) {
                        changedFile = file;
                    }
                });
            },

            getGrademapByResult(result) {
                let correctGrademap = null;
                this.charon.grademaps.forEach((grademap) => {
                    if (result.grade_type_code == grademap.grade_type_code) {
                        correctGrademap = grademap;
                    }
                });

                return correctGrademap;
            },

            findResultById(id) {
                let matchingResult = null;

                if (this.submission === null) {
                    return null;
                }

                this.submission.results.forEach((result) => {
                    if (result.id == id) {
                        matchingResult = result;
                    }
                });

                return matchingResult;
            },

            hasOutput(object, kind) {
                return object[kind] !== null && object[kind].length > 0;
            },

            getOutputs() {
                let outputs = [];

                if (this.hasOutput(this.submission, 'stdout')) {
                    outputs.push({
                        value: 'submission__stdout',
                        title: 'Submission stdout'
                    });
                }
                if (this.hasOutput(this.submission, 'stderr')) {
                    outputs.push({
                        value: 'submission__stderr',
                        title: 'Submission stderr'
                    });
                }

                this.submission.results.forEach(result => {
                    if (this.hasOutput(result, 'stdout')) {
                        outputs.push({
                            value: 'result__stdout__' + result.id,
                            title: this.getGrademapByResult(result).name + ' stdout'
                        });
                    }
                    if (this.hasOutput(result, 'stderr')) {
                        outputs.push({
                            value: 'result__stderr__' + result.id,
                            title: this.getGrademapByResult(result).name + ' stderr'
                        });
                    }
                });

                return outputs;
            }
        }
    }
</script>
