<template>

    <instance-form-fieldset>
        <template slot="title">{{ naming_title }}</template>

        <slot>

            <charon-text-input
                    input_name="name"
                    :input_label="task_name_label"
                    required="true"
                    :input_value="task_name_value">
            </charon-text-input>

        </slot>
    </instance-form-fieldset>

</template>

<script>
    import InstanceFormFieldset from './../form/InstanceFormFieldset.vue';
    import CharonTextInput from './../form/CharonTextInput.vue';

    export default {
        props: [
            'task_name_label', 'task_name_value', 'naming_title'
        ],

        components: {
            InstanceFormFieldset, CharonTextInput
        }
    }
</script>
