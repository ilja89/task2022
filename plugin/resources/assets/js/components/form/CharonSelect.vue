<template>
    <div :id="'id_' + name + '_container'" class="fcontainer clearfix">
        <div :id="'fitem_id_' + name" class="fitem fitem_select">

            <div class="fitemtitle"><label :for="'id_' + name">{{ label }}</label></div>

            <p class="input-helper" v-if="helper_text !== null" v-html="helper_text"></p>

            <div class="felement">
                <select :name="name"
                        :id="'id_' + name"
                        v-model="input_value"
                        @change="onInputChanged">
                    <option
                            v-for="option in options"
                            :value="option[key_field]">
                            <!--:selected="option[key_field] === value ? 'selected' : ''">-->
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
        },

        data() {
            return {
                input_value: this.value === '' ? this.options[0][this.key_field] : this.value,
            };
        },
    }
</script>
