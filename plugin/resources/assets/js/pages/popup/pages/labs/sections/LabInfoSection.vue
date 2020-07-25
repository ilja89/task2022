<template>

    <fieldset class="clearfix collapsible" id="id_modstandardelshdr">
        <div v-if="show_info" class="topic-open">
            <legend v-on:click="show_info = !show_info" class="ftoggler">Lab info</legend>
        </div>
        <div v-else class="topic-closed">
            <legend v-on:click="show_info = !show_info" class="ftoggler">Lab info</legend>
        </div>

        <div v-if="show_info" class="fcontainer clearfix fitem">


            <lab-info-row :lab_given="lab_given">
            </lab-info-row>

        </div>

    </fieldset>

</template>

<script>
    import { Translate } from '../../../../../mixins';
    import LabInfoRow from "../components/LabInfoRow";

    export default {
        mixins: [ Translate ],

        components: { LabInfoRow },

        data() {
            return {
                show_info: true,
            }
        },

        props: {
            lab_given: {required: true}
        },

        computed: {
            isEditing() {
                return window.isEditing;
<<<<<<< HEAD
            },
        },
=======
            }
        }
>>>>>>> dev
    }
</script>
