<template>
    <instance-form-fieldset>
        <template slot="title">{{ translate('grading_title') }}</template>

        <slot>

            <charon-select
                    :label="translate('grading_method_label')"
                    name="grading_method"
                    :options="form.grading_methods"
                    :selected="form.fields.grading_method"
                    @input-was-changed="onGradingMethodChanged"
            >
            </charon-select>

        </slot>

    </instance-form-fieldset>
</template>

<script>
    import InstanceFormFieldset from '../form/InstanceFormFieldset.vue';
    import CharonSelect from '../form/CharonSelect.vue';

    import Translate from '../../mixins/translate';
    import EmitEventOnInputChange from '../../mixins/emitEventOnInputChange';

    export default {
        mixins: [ Translate, EmitEventOnInputChange ],

        components: { InstanceFormFieldset, CharonSelect },

        props: [ 'form' ],
    }
</script>
