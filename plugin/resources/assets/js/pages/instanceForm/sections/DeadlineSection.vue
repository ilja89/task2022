<template>

    <fieldset class="clearfix collapsible" id="id_modstandardelshdr_DS">

        <legend class="ftoggler">{{ translate('deadlines') }}</legend>

        <div class="fcontainer clearfix fitem">

            <deadline-row
                    v-for="(deadline, index) in form.fields.deadlines"
                    :deadline="deadline"
                    :key="index"
                    :groups="form.groups"
                    :id="index">
            </deadline-row>

            <button type="button" class="add-deadline-btn" @click="onAddDeadlineClicked">Add Deadline</button>

            <br>

            <label v-if="isEditing">
                <input type="checkbox" name="recalculate_grades" v-model="form.recalculate_grades">
                Recalculate grades
            </label>

        </div>

    </fieldset>

</template>

<script>
    import { Translate } from '../../../mixins';
    import { DeadlineRow } from '../components';

    export default {
        mixins: [ Translate ],

        components: { DeadlineRow },

        props: {
            form: { required: true }
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
