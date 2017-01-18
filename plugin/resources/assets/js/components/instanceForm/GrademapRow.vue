<template>
    <div class="grademap-row">
        <div class="grademap-field">
            <label :for="'grademaps[' + grade_type_code + '][grademap_name]'">{{ translate('grade_name_label') }}</label><br>
            <input class="form-control" :name="'grademaps[' + grade_type_code + '][grademap_name]'" type="text"
                   v-model="grademap.name">
        </div>
        <div class="grademap-field">
            <label :for="'grademaps[' + grade_type_code + '][max_points]'">{{ translate('max_points_label') }}</label><br>
            <input class="form-control" :name="'grademaps[' + grade_type_code + '][max_points]'" type="number"
                   v-model="grademap.max_points">
        </div>
        <div class="grademap-field">
            <label :for="'grademaps[' + grade_type_code + '][id_number]'">{{ translate('id_number_label') }}</label><br>
            <input class="form-control" :name="'grademaps[' + grade_type_code + '][id_number]'" type="text"
                   v-model="grademap.id_number">
        </div>
    </div>
</template>

<script>
    import Translate from '../../mixins/translate';

    export default {
        mixins: [ Translate ],

        props: {
            initial_grademap: {required: false},
            grade_type_code: {required: true}
        },

        data() {
            return {
                grademap: {
                    name: '',
                    max_points: 1,
                    id_number: name.toLowerCase().replace(/ /g, '-'),
                }
            };
        },

        mounted() {
            if (this.initial_grademap != null) {
                this.grademap = this.initial_grademap;
            }
        }
    }
</script>
