<template>

    <fieldset class="clearfix collapsible" id="id_modstandardelshdr">
        <div v-if="show_info" class="topic-open">
            <legend v-on:click="show_info = !show_info" class="ftoggler">Lab info</legend>
        </div>
        <div v-else class="topic-closed">
            <legend v-on:click="show_info = !show_info" class="ftoggler">Lab info</legend>
        </div>

        <div v-if="show_info" class="fcontainer clearfix fitem">

            <lab-info-row :deadline="deadline"
                          :teachers="form.teachers">
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
                deadline: {deadline_time: {time: '12-07-2020 23:45'}, teachers: []},
                teachers: [
                    {name: 'Ago', id: 1},
                    {name: 'Kadri', id: 2},
                    {name: 'Orav', id: 3}
                ]  // actually form.teachers
            }
        },

        props: {
            form: {required: true}
        },

        computed: {
            isEditing() {
                return window.isEditing;
            },
        },

        methods: {
            onAddDeadlineClicked() {
                VueEvent.$emit('deadline-was-added');
            },

        },
    }
</script>
