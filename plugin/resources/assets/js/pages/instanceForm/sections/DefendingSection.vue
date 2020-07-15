<template>

    <fieldset class="clearfix collapsible" id="id_modstandardelshdr">

        <legend class="ftoggler">Defense</legend>

        <div class="fcontainer clearfix fitem">

            <defense-row
                    :defense="form_fields_defense.def"
                    :labs="form_fields_defense.labs">
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
                    labs: [{name: 'T12', date: new Date('14-07-2020 12:00')},
                        {name: 'T14', date: new Date('14-07-2020 14:00')},
                        {name: 'N9', date: new Date('16-07-2020 9:00')},
                        {name: 'N16', date: new Date('16-07-2020 16:00')}],
                    def: {
                        deadline: {time: new Date('09-07-2020 11:45')},
                        duration: 10,
                        labs: [],
                        teacher: true
                    }
                }
            }
        },

        computed: {
            isEditing() {
                return window.isEditing;
            },
        },

    }
</script>
