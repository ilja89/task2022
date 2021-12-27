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

            <br />

            <label v-if="isEditing">
                <input type="checkbox" name="recalculate_grades" v-model="form.recalculate_grades">
                Recalculate grades
            </label>
            <br />
            <label v-if="isEditing && form.recalculate_grades === true && form.fields.grading_method_code === 3"
                   class="red--text">

                Warning: recalculating grades with grading method 'prefer_best_each_test_grade'
                may take a lot of time to finish, depending on the count of submissions.
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
