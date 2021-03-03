<template>

        <v-card class="mx-auto mb-4" outlined light raised>
            <v-container class="spacing-playground pa-3" fluid>
                <div class="fcontainer clearfix fitem labs-field">

                    <label>Weeks</label>

                    <p class="input-helper-labs">Choose weeks when this lab session takes place. Course start date is
                        considered the first week.</p>

                    <multiselect :disabled="this.lab.id !== undefined" v-model="lab.weeks" :options="weeks"
                                 :multiple="true"
                                 :close-on-select="false" placeholder="Select weeks"
                                 :clear-on-select="true" class="multiselect__width" style="width: 350px">
                    </multiselect>
                </div>

            </v-container>
        </v-card>

</template>

<script>
    import {Translate} from '../../../../../mixins';
    import Multiselect from 'vue-multiselect';

    export default {
        mixins: [Translate],

        components: {Multiselect},

        data() {
            return {
                show_multiple: true,
                weeks: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16]
            }
        },

        props: {
            lab: {required: true}
        },

        computed: {
            isEditing() {
                return window.isEditing;
            },
        },

    }
</script>

<style src="../../../../../../../../../node_modules/vue-multiselect/dist/vue-multiselect.min.css"></style>
