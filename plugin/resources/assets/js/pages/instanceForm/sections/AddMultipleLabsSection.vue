<template>

    <fieldset class="clearfix collapsible" id="id_modstandardelshdr">

        <legend class="ftoggler">Add multiple lab sessions</legend>

        <div class="fcontainer clearfix fitem">

            <label>Weeks</label>

            <p class="input-helper">Choose weeks when this lab session takes place.</p>

            <select v-model="chosen_weeks">
                <option v-for="week in weeks" :value="week">
                    {{ week }}
                </option>
            </select>

        </div>

    </fieldset>

</template>

<script>
    import { Translate } from '../../../mixins';

    export default {
        mixins: [ Translate ],

        data() {
            return {
                weeks: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16],
                chosen_weeks: []
            }
        },

        computed: {
            isEditing() {
                return window.isEditing;
            },
        },

    }
</script>
