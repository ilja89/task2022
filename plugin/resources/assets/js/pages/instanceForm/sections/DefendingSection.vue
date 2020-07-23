<template>

    <fieldset class="clearfix collapsible" id="id_modstandardelshdr">

        <legend class="ftoggler">Defense</legend>

        <div class="fcontainer clearfix fitem">

            <defense-row
                    :defense="form_fields_defense.def"
                    :form="form">
            </defense-row>

            <br>

        </div>

    </fieldset>

</template>

<script>
    import { Translate } from '../../../mixins';
    import { DeadlineRow } from '../components';
    import DefenseRow from "../components/DefenseRow";

    export default {
        mixins: [ Translate ],

        components: {DefenseRow, DeadlineRow },

        props: {
            form: { required: true }
        },

        data() {
            return {
                form_fields_defense: {
                    labs: [{id:1, start: new Date('2020-08-07 12:00')},
                        {id: 2, start: new Date('2020-08-10 14:00')},
                        {id: 3, start: new Date('2020-08-12 9:00')},
                        {id: 4, start: new Date('2020-08-13 16:00')},
                        {id: 5, start: new Date('2020-08-20 16:00')}],
                    def: {
                        deadline: {time: new Date('09-07-2020 11:45')},
                        duration: 10,
                        labs: [],
                        teacher: true
                    }
                },
            }
        },

        computed: {
            isEditing() {
                return window.isEditing;
            },
        }

    }
</script>
