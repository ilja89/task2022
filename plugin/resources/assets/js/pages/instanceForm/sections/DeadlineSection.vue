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

            <div v-if="isEditing">

                <label>
                    <input type="checkbox" name="recalculate_grades" v-model="form.recalculate_grades">
                    {{ translate('recalculate_grades_label') }}
                </label>

                <br />

                <label v-if="form.recalculate_grades === true && form.fields.grading_method_code === 3"
                       class="red--text">

                    {{ translate('warning_general_label') }}:
                    {{ translate('warning_recalculation_time_prefer_best_each_grade_label') }}
                </label>

            </div>

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
