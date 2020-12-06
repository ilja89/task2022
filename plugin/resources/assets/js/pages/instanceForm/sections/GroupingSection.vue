<template>

    <fieldset class="clearfix collapsible" id="id_modstandardelshdr">

        <legend class="ftoggler">{{ translate('grouping') }}</legend>

        <div class="fcontainer clearfix fitem">

        <charon-select
                name="grouping_id"
                :options="form.groupings"
                key_field="id"
                :value="form.fields.grouping_id"
                :helper_text="translate('grouping_selection_helper')"
                @input-was-changed="onGroupingChanged"  label="">
        </charon-select>

        <br />
        <br />
        </div>

    </fieldset>

</template>

<script>
    import { EmitEventOnInputChange, Translate } from '../../../mixins';
    import { CharonSelect } from '../../../components/form';

    export default {
        mixins: [ Translate, EmitEventOnInputChange ],

        components: { CharonSelect },

        props: {
            form: { required: true }
        },
        computed: {
            isEditing() {
                return window.isEditing;
            },
        },
        methods: {
            onGroupingChanged(grouping_id) {
                VueEvent.$emit('grouping-was-changed', grouping_id);
            }
        }
    }
</script>