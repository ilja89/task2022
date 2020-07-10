<template>

    <fieldset class="clearfix collapsible" id="id_modstandardelshdr">

        <legend class="ftoggler">Lab info</legend>

        <div class="fcontainer clearfix fitem">

            <lab-info-row :deadline="deadline"
                          :teachers="teachers">
            </lab-info-row>

        </div>

    </fieldset>

</template>

<script>
    import { Translate } from '../../../mixins';
    import { LabInfoRow } from '../components';

    export default {
        mixins: [ Translate ],

        components: { LabInfoRow },

        data() {
            return {
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
