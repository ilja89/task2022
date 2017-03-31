<template>
    <div class="deadline-row is-flex">
        <div class="deadline-field is-flex-1">
            <label>{{ translate('deadline_label') }}</label>
            <datepicker :datetime="deadline.deadline_time"></datepicker>
            <input type="hidden" :name="'deadlines[' + id + '][deadline_time]'" :value="deadline.deadline_time.time">
        </div>
        <div class="deadline-field is-flex-1">
            <label>{{ translate('percentage_label') }}</label>
            <input type="number"
                   :name="'deadlines[' + id + '][percentage]'"
                   v-model="deadline.percentage"
                   class="form-control">
        </div>
        <div class="deadline-field is-flex-1">
            <label>{{ translate('group_label') }}</label>
            <input type="number"
                   :name="'deadlines[' + id + '][group_id]'"
                   v-model="deadline.group_id"
                   class="form-control">
        </div>
        <div class="deadline-field is-flex-1">
            <button type="button" @click="onRemoveClicked" class="remove-deadline-btn">Remove Deadline</button>
        </div>
    </div>
</template>

<script>
    import { Datepicker } from '../../../components/partials';
    import { Translate } from '../../../mixins';

    export default {
        mixins: [ Translate ],

        components: { Datepicker },

        props: {
            deadline: { required: true },
            id: { required: true }
        },

        methods: {
            onRemoveClicked() {
                VueEvent.$emit('deadline-was-removed', this.id);
            }
        }
    }
</script>
