<template>

    <fieldset class="clearfix collapsible" id="id_modstandardelshdr">

        <legend class="ftoggler">Deadlines</legend>

        <div class="fcontainer clearfix fitem">

            <deadline-row
                    v-for="(deadline, index) in form.fields.deadlines"
                    :deadline="deadline"
                    :id="index">
            </deadline-row>

            <button type="button" class="add-deadline-btn" @click="onAddDeadlineClicked">Add Deadline</button>

        </div>

    </fieldset>

</template>

<script>
    import { Translate } from '../../mixins';
    import DeadlineRow from './DeadlineRow.vue';

    export default {
        mixins: [ Translate ],

        components: { DeadlineRow },

        props: {
            form: { required: true }
        },

        methods: {
            onAddDeadlineClicked() {
                VueEvent.$emit('deadline-was-added');
            }
        }
    }
</script>
