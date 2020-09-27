<template>
    <div>
        <div
                v-if="outputs.length > 0"
                class="tabs-right  select-container  output-select"
        >
            <popup-select
                    size="medium"
                    name="output"
                    :options="outputs"
                    value-key="slug"
                    placeholder-key="title"
                    v-model="activeOutputSlug"
            />
        </div>

        <v-card class="mx-auto" max-height="900" max-width="80vw" outlined raised v-if="outputs.length">
            <pre style="max-height: 900px;overflow: auto">{{ activeOutput }}</pre>
        </v-card>

    </div>
</template>

<script>
    import {mapState} from 'vuex'
    import {Output} from '../../../api'
    import PopupSelect from './PopupSelect'

    export default {

        components: {PopupSelect},

        props: {
            grademaps: {required: true},
        },

        data() {
            return {
                outputs: [],
                activeOutputSlug: null,
            }
        },

        computed: {
            ...mapState([
                'submission',
            ]),

            activeOutput() {
                if (this.activeOutputSlug === null) {
                    return 'No output selected.'
                }

                const activeOutput = this.outputs.find(output => {
                    return output.slug === this.activeOutputSlug
                })

                if (typeof activeOutput !== 'undefined') {
                    return activeOutput.content
                }

                return ''
            }
        },

        watch: {
            submission() {
                this.getOutputs()
            }
        },

        created() {
            this.getOutputs()
        },

        methods: {
            hasOutput(object, kind) {
                return object !== null && object[kind] !== null && object[kind].length > 0
            },

            getGrademapByResultId(resultId) {

                let result = this.submission.results.find(result => {
                    return result.id == resultId
                })

                if (typeof result === 'undefined') {
                    return null
                }

                let correctGrademap = null
                this.grademaps.forEach((grademap) => {
                    if (result.grade_type_code == grademap.grade_type_code) {
                        correctGrademap = grademap
                    }
                })

                return correctGrademap
            },

            getOutputs() {
                Output.findBySubmission(this.submission.id, outputs => {
                    this.outputs = []

                    if (this.hasOutput(outputs['submission'], 'stdout')) {
                        this.outputs.push({
                            slug: 'submission__stdout',
                            title: 'Submission stdout',
                            content: outputs['submission']['stdout']
                        })
                    }
                    if (this.hasOutput(outputs['submission'], 'stderr')) {
                        this.outputs.push({
                            slug: 'submission__stderr',
                            title: 'Submission stderr',
                            content: outputs['submission']['stderr']
                        })
                    }

                    for (let resultId in outputs['results']) {
                        const grademap = this.getGrademapByResultId(resultId)
                        if (this.hasOutput(outputs['results'][resultId], 'stdout')) {
                            this.outputs.push({
                                slug: 'result__stdout__' + resultId,
                                title: (grademap ? grademap.name : '') + ' stdout',
                                content: outputs['results'][resultId]['stdout']
                            })
                        }
                        if (this.hasOutput(outputs['results'][resultId], 'stderr')) {
                            this.outputs.push({
                                slug: 'result__stderr__' + resultId,
                                title: (grademap ? grademap.name : '') + ' stderr',
                                content: outputs['results'][resultId]['stderr']
                            })
                        }
                    }
                })
            },
        },
    }
</script>
