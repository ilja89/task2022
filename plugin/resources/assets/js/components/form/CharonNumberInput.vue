<template>
    <div :id="'id_' + name + '_container'" class="fcontainer clearfix">
        <div :id="'fitem_id_' + name" class="fitem fitem_ftext" :class="required ? 'required' : ''">
            <div class="fitemtitle">
                <label :for="'id_' + name" :class="required ? 'required' : ''">{{ label }}</label>
            </div>
            <div class="felement ftext">
                <input :name="name" type="number" :required="required"
                       :id="'id_' + name" class="form-control" v-model="value" v-on:keyup="onInputChanged">
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: [
            'name', 'required', 'label', 'input_value'
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
        }
    }
</script>
