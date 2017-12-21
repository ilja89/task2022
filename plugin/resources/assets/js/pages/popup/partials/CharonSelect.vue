<template>
    <popup-select
        name="charon"
        :options="charons"
        value-key="id"
        placeholder-key="name"
        size="medium"
        v-model="activeCharonId"
    />
</template>

<script>
    import { mapGetters, mapState, mapActions } from 'vuex'
    import { Charon } from '../../../api'
    import PopupSelect from './PopupSelect'

    export default {
        components: { PopupSelect },

        data() {
            return {
                charons: [],
            }
        },

        computed: {
            ...mapGetters([
                'courseId',
            ]),

            ...mapState([
                'charon',
            ]),

            activeCharonId: {
                get() {
                    return this.charon ? this.charon.id : null
                },
                set(value) {
                    this.charons
                        .filter(charon => charon.id === value)
                        .forEach(charon => this.updateCharon({ charon }))

                    this.updateSubmission({ submission: null })
                },
            },
        },

        mounted() {
            this.refreshCharons()
            VueEvent.$on('refresh-page', this.refreshCharons)
        },

        methods: {
            ...mapActions([
                'updateCharon',
                'updateSubmission',
            ]),

            refreshCharons() {
                Charon.all(this.courseId, charons => {
                    this.charons = charons
                    // if (this.activeCharonId === null && charons.length > 0) {
                    //     this.activeCharonId = charons[0].id
                    // }
                })
            },
        },
    }
</script>
