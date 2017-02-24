<template>
    <div :id="'id_' + input_name + '_container'" class="fcontainer clearfix">
        <div :id="'fitem_id_' + input_name" class="fitem fitem_ftext" :class="required ? 'required' : ''">
            <div class="fitemtitle">
                <label :for="'id_' + input_name" :class="required ? 'required' : ''">{{ input_label }}</label>
            </div>
            <div class="felement ftext">
                <input size="64" :name="input_name" type="text" :required="required"
                       :id="'id_' + input_name" class="form-control" v-model="value" @keyup="onInputChanged">
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: [
            'input_name', 'required', 'input_label', 'input_value'
        ],

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
