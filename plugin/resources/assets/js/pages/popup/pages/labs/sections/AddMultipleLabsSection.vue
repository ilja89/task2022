<template>

    <fieldset class="clearfix collapsible" id="id_modstandardelshdr">
        <div v-if="show_multiple" class="topic-open">
            <legend v-on:click="show_multiple = !show_multiple" class="ftoggler">Add multiple lab sessions</legend>
        </div>
        <div v-else class="topic-closed">
            <legend v-on:click="show_multiple = !show_multiple" class="ftoggler">Add multiple lab sessions</legend>
        </div>

        <div v-if="show_multiple" class="fcontainer clearfix fitem labs-field" style="margin-bottom: 10px">

            <label>Weeks</label>

            <p class="input-helper-labs">Choose weeks when this lab session takes place.</p>

            <multiselect v-model="lab.weeks" :options="weeks" :multiple="true"
                         :close-on-select="false" placeholder="Select weeks"
                         :clear-on-select="true" class="multiselect__width" style="width: 350px">
            </multiselect>

        </div>

    </fieldset>

</template>

<script>
    import { Translate } from '../../../../../mixins';
    import Multiselect from 'vue-multiselect';

    export default {
        mixins: [ Translate ],

        components: { Multiselect },

        data() {
            return {
                show_multiple: true,
<<<<<<< HEAD
                weeks: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16],
=======
                weeks: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16]
>>>>>>> dev
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
