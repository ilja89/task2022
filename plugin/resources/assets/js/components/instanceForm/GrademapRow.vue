<template>
    <div class="grademap-row">
        <div class="grademap-field">
            <label :for="'grademaps[' + grademap.grade_type_code + '][grademap_name]'">{{ translate('grade_name_label') }}</label><br>
            <input class="form-control"
                   :name="'grademaps[' + grademap.grade_type_code + '][grademap_name]'"
                   type="text"
                   v-model="grademap.name"
                   @keyup="onGradeNameChange">
        </div>
        <div class="grademap-field">
            <label :for="'grademaps[' + grademap.grade_type_code + '][max_points]'">{{ translate('max_points_label') }}</label><br>
            <input class="form-control" :name="'grademaps[' + grademap.grade_type_code + '][max_points]'" type="number"
                   v-model="grademap.max_points">
        </div>
        <div class="grademap-field">
            <label :for="'grademaps[' + grademap.grade_type_code + '][id_number]'">{{ translate('id_number_label') }}</label><br>
            <input class="form-control" :name="'grademaps[' + grademap.grade_type_code + '][id_number]'" type="text"
                   v-model="grademap.id_number">
        </div>
    </div>
</template>

<script>
    import Translate from '../../mixins/translate';

    export default {
        mixins: [ Translate ],

        props: {
            grademap: { required: true },
        },

        methods: {
            onGradeNameChange() {
                this.grademap.id_number = this.grademap.name.replace(/-/g, '').replace(/ /g, '_').replace(/__/g, '_');
            }
        }
    }
</script>
