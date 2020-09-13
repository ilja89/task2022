<template>
    <v-card class="mx-auto mb-16" outlined light raised>
        <v-container class="spacing-playground pa-3" fluid>

            <div class="fcontainer clearfix fitem">

                <lab-info-row :lab_given="lab_given" :teachers="teachers">
                </lab-info-row>

            </div>
        </v-container>
    </v-card>
</template>

<script>
    import {Translate} from '../../../../../mixins';
    import LabInfoRow from "../components/LabInfoRow";

    export default {
        mixins: [Translate],

        components: {LabInfoRow},

        data() {
            return {
                show_info: true,
            }
        },

        props: {
            lab_given: {required: true},
            teachers: {required: true}
        },

        computed: {
            isEditing() {
                return window.isEditing;
            }
        }
    }
</script>
