<template>
    <div>
        <p class="control tabs-right select-container output-select" v-if="outputs.length > 0">
            <span class="select  is-medium">

                <select name="output"
                        v-model="activeOutputSlug">

                    <option v-for="output in outputs"
                            :value="output.slug">
                        {{ output.title }}
                    </option>

                </select>

            </span>
        </p>

        <pre class="output-content" v-if="outputs.length > 0">{{ activeOutput }}</pre>
    </div>
</template>

<script>
    import { Output } from '../../../models';

    export default {

        props: {
            submission: { required: true },
            grademaps: { required: true },
        },

        data() {
            return {
                outputs: [],
                activeOutputSlug: null,
            };
        },

        computed: {
            activeOutput() {
                if (this.activeOutputSlug === null) {
                    return 'No output selected.';
                }

                const activeOutput = this.outputs.find(output => {
                    return output.slug === this.activeOutputSlug;
                });

                if (typeof activeOutput !== 'undefined') {
                    return activeOutput.content;
                }

                return '';
            }
        },

        watch: {
            submission() {
                this.getOutputs();
            }
        },

        mounted() {
            this.getOutputs();
        },

        methods: {
            hasOutput(object, kind) {
                return object !== null && object[kind] !== null && object[kind].length > 0;
            },

            getGrademapByResultId(resultId) {

                let result = this.submission.results.find(result => {
                    return result.id == resultId;
                });

                if (typeof result === 'undefined') {
                    return null;
                }

                let correctGrademap = null;
                this.grademaps.forEach((grademap) => {
                    if (result.grade_type_code == grademap.grade_type_code) {
                        correctGrademap = grademap;
                    }
                });

                return correctGrademap;
            },

            getOutputs() {
                Output.findBySubmission(this.submission.id, outputs => {
                    this.outputs = [];

                    if (this.hasOutput(outputs['submission'], 'stdout')) {
                        this.outputs.push({
                            slug: 'submission__stdout',
                            title: 'Submission stdout',
                            content: outputs['submission']['stdout']
                        });
                    }
                    if (this.hasOutput(outputs['submission'], 'stderr')) {
                        this.outputs.push({
                            slug: 'submission__stderr',
                            title: 'Submission stderr',
                            content: outputs['submission']['stderr']
                        });
                    }

                    for (let resultId in outputs['results']) {
                        if (this.hasOutput(outputs['results'][resultId], 'stdout')) {
                            this.outputs.push({
                                slug: 'result__stdout__' + resultId,
                                title: this.getGrademapByResultId(resultId).name + ' stdout',
                                content: outputs['results'][resultId]['stdout']
                            });
                        }
                        if (this.hasOutput(outputs['results'][resultId], 'stderr')) {
                            this.outputs.push({
                                slug: 'result__stderr__' + resultId,
                                title: this.getGrademapByResultId(resultId).name + ' stderr',
                                content: outputs['results'][resultId]['stderr']
                            });
                        }
                    }

                    if (this.outputs.length > 0) {
                        this.activeOutputSlug = this.outputs[0].slug;
                    }
                });
            }
        }
    }
</script>
