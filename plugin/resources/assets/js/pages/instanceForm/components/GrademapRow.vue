<template>
    <div class="grademap-row">
        <div class="grademap-field">
            <label :for="'grademaps[' + grademap.grade_type_code + '][grademap_name]'">{{ translate('grade_name_label') }}</label><br>
            <p class="input-helper" v-html="translate('grade_name_helper')"></p>
            <input class="form-control"
                   :name="'grademaps[' + grademap.grade_type_code + '][grademap_name]'"
                   type="text"
                   v-model="grademap.name"
                   @keyup="onGradeNameChange">
        </div>
        <div class="grademap-field">
            <label :for="'grademaps[' + grademap.grade_type_code + '][max_points]'">{{ translate('max_points_label') }}</label><br>
            <p class="input-helper" v-html="translate('max_points_grade_helper')"></p>
            <input class="form-control" :name="'grademaps[' + grademap.grade_type_code + '][max_points]'" type="number"
                   v-model="grademap.max_points" step="0.01">
        </div>
        <div class="grademap-field">
            <label :for="'grademaps[' + grademap.grade_type_code + '][id_number]'">{{ translate('id_number_label') }}</label><br>
            <p class="input-helper" v-html="translate('id_number_helper')"></p>
            <input class="form-control" :name="'grademaps[' + grademap.grade_type_code + '][id_number]'" type="text"
                   v-model="grademap.id_number">
        </div>
      <div v-if="grademap.grade_type_code > 1000" class="grademap-field">
        <label :for="'grademaps[' + grademap.grade_type_code + '][persistent]'">{{ translate('grade_persistent_label') }}</label><br>
        <p class="input-helper" v-html="translate('grade_persistent_helper')"></p>
        <input type="checkbox" class="form-control" :name="'grademaps[' + grademap.grade_type_code + '][persistent]'"
               value="1" v-model="grademap.persistent" v-clck="log()">
      </div>
    </div>
</template>

<script>
    import { Translate } from '../../../mixins';

    export default {
        mixins: [ Translate ],

        props: {
          // TODO: persistent
            persistent: { required: true },
            grademap: { required: true },
            formula: { required: false, default: '' },
        },

        methods: {
            onGradeNameChange() {
                if (this.formula.length === 0) {
                    this.grademap.id_number = this.grademap.name.replace(/-/g, '').replace(/ /g, '_').replace(/__/g, '_');
                }
            },

            log() {
              console.log(this.grademap);
              console.log('pers: ' + this.persistent);
            }
        }
    }
</script>
