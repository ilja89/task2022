<template>
    <div>
        <div v-if="outputs.length > 0" class="tabs-right  select-container  output-select">
            <popup-select
                    size="medium"
                    name="output"
                    :options="outputs"
                    value-key="slug"
                    placeholder-key="title"
                    v-model="activeOutputSlug"
            />
        </div>

        <v-card :key="submission.id" class="mx-auto" max-height="900" max-width="80vw" outlined raised
                v-if="outputs.length">
          <pre v-html="activeOutput" style="max-height: 900px"></pre>
        </v-card>

    </div>
</template>

<script>
    import {mapState} from 'vuex'
    import PopupSelect from './PopupSelect'

    export default {

        components: {PopupSelect},

        props: {
            submission: {required: true}
        },

        data() {
            return {
                outputs: [],
                activeOutputSlug: null,
            }
        },

        computed: {

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

        created() {
            this.getOutputs()
        },

        watch: {
            submission() {
                this.getOutputs()
            }
        },

        methods: {
            hasOutput(object, kind) {
                return object !== null && object[kind] !== null && object[kind].length > 0
            },

            getOutputs() {
                this.outputs = []

                this.outputs.push({
                    slug: 'submission__stdout',
                    title: 'Submission stdout',
                    content: this.submission['stdout']
                })

                this.outputs.push({
                    slug: 'submission__stderr',
                    title: 'Submission stderr',
                    content: this.submission['stderr']
                })
            },
        },
    }
</script>
