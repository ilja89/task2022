<template>
    <div class="deadline-row is-flex">
        <div class="deadline-field">
            <label>{{ translate('deadline_label') }}</label>
            <p class="input-helper-labs" v-html="translate('deadline_helper')"></p>
            <datepicker :datetime="deadline.deadline_time"></datepicker>
            <input type="hidden" :name="'deadlines[' + id + '][deadline_time]'" :value="deadline.deadline_time.time">
        </div>

        <div class="deadline-field">
            <label>{{ translate('percentage_label') }}</label>
            <p class="input-helper-labs" v-html="translate('percentage_helper')"></p>
            <input type="number"
                   :name="'deadlines[' + id + '][percentage]'"
                   v-model="deadline.percentage"
                   class="form-control">
        </div>

        <div class="deadline-field is-flex-1">
            <label>{{ translate('group_label') }}</label>

            <p class="input-helper" v-html="translate('group_helper')"></p>

            <select v-model="deadline.group_id" :name="'deadlines[' + id + '][group_id]'">
                <option v-for="group in groups" :value="group.id">
                    {{ group.name }}
                </option>
            </select>

            <!--<input type="number"-->
                   <!--:name="'deadlines[' + id + '][group_id]'"-->
                   <!--v-model="deadline.group_id"-->
                   <!--class="form-control">-->
        </div>

        <div class="deadline-field">
            <button type="button" @click="onRemoveClicked" class="remove-deadline-btn">Remove Deadline</button>
        </div>
    </div>
</template>

<script>
    import { Datepicker } from '../../../components/partials';
    import { Translate } from '../../../mixins';
    import { CharonSelect } from '../../../components/form';

    export default {
        mixins: [ Translate ],

        components: { Datepicker, CharonSelect },

        props: {
            deadline: { required: true },
            id: { required: true },
            groups: { required: true },
        },

        methods: {
            onRemoveClicked() {
                VueEvent.$emit('deadline-was-removed', this.id);
            },
        },
    }
</script>
