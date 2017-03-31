<template>
    <div :id="'id_' + name + '_container'" class="fcontainer clearfix">
        <div :id="'fitem_id_' + name" class="fitem fitem_ftext" :class="required ? 'required' : ''">
            <div class="fitemtitle">
                <label :for="'id_' + name" :class="required ? 'required' : ''">{{ label }}</label>
            </div>
            <p class="input-helper" v-if="helper_text !== null" v-html="helper_text"></p>
            <div class="felement ftext">
                <input :name="name" type="number" :required="required" step="0.01"
                       :id="'id_' + name" class="form-control" v-model="value" v-on:keyup="onInputChanged">
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: {
            name: { required: true },
            required: { required: false, default: false },
            label: { required: true },
            input_value: { required: true },
            helper_text: { required: false, default: null },
        },

        data() {
            return {
                value: ''
            };
        },

        watch: {
            input_value() {
                this.value = this.input_value;
            }
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
