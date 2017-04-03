<template>
    <div :id="'id_' + input_name + '_container'" class="fcontainer clearfix">
        <div :id="'fitem_id_' + input_name" class="fitem fitem_ftext" :class="required ? 'required' : ''">
            <div class="fitemtitle">
                <label :for="'id_' + input_name" :class="required ? 'required' : ''">{{ input_label }}</label>
            </div>
            <p class="input-helper" v-if="helper_text !== null" v-html="helper_text"></p>
            <div class="felement ftext">
                <input size="64" :name="input_name" type="text" :required="required"
                       :id="'id_' + input_name" class="form-control" v-model="value" @keyup="onInputChanged">
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: {
            input_name: { required: true },
            required: { required: false, default: false },
            input_label: { required: true },
            input_value: { required: true },
            helper_text: { required: false, default: null }
        },

        data() {
            return {
                value: ''
            };
        },

        mounted() {
            this.value = this.input_value;
        },

        methods: {
            onInputChanged(event) {
                this.$emit('input-was-changed', this.value);
            }
        },

        watch: {
            input_value() {
                this.value = this.input_value;
            }
        }
    }
</script>
