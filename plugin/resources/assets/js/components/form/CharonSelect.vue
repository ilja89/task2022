<template>
    <div :id="'id_' + name + '_container'" class="fcontainer clearfix">
        <div :id="'fitem_id_' + name" class="fitem fitem_select">

            <div class="fitemtitle"><label :for="'id_' + name">{{ label }}</label></div>

            <p class="input-helper" v-if="helper_text !== null" v-html="helper_text"></p>

            <div class="felement">
                <select :name="name"
                        class="custom-select"
                        :id="'id_' + name"
                        v-model="input_value"
                        :disabled="disabled"
                        @change="onInputChanged">
                    <option v-if="include_empty"></option>
                    <option
                            v-for="option in options"
                            :value="option[key_field]">
                        {{ option.name }}
                    </option>
                </select>
            </div>

        </div>
    </div>
</template>

<script>
    import { FormElement } from '../../mixins'

    export default {

        mixins: [ FormElement ],

        props: {
            options: { required: true },
            key_field: { required: false, default: 'code' },
            helper_text: { required: false, default: null },
            include_empty: { required: false, default: false },
            disabled: { required: false, default: false}
        },

        data() {
            return {
                input_value: (this.value === '' && this.options.length) ? this.options[0][this.key_field] : this.value,
            };
        },
    }
</script>
