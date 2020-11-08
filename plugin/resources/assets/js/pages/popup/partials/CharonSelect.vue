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
    import {mapGetters, mapState, mapActions} from 'vuex'
    import PopupSelect from './PopupSelect'

    export default {
        components: {PopupSelect},

        props: {
            active: {required: false}
        },

        computed: {
            ...mapGetters([
                'courseId',
            ]),

            ...mapState([
                'charon',
                'charons'
            ]),

            activeCharonId: {
                get() {
                    return this.charon ? this.charon.id : null
                },

                set(value) {
                    this.active = value;
                    this.charons
                        .filter(charon => charon.id === value)
                        .forEach(charon => this.updateCharon({charon}))

                    this.updateSubmission({submission: null})
                },
            },
        },

        methods: {
            ...mapActions([
                'updateCharon',
                'updateSubmission',
            ])
        },
    }
</script>
