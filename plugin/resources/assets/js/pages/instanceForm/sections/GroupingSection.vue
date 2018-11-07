<template>

    <fieldset class="clearfix collapsible" id="id_modstandardelshdr">

        <legend class="ftoggler">{{ translate('grouping') }}</legend>

        <div class="fcontainer clearfix fitem">

        <charon-select
            :helper_text="translate('grouping_selection_helper')"
            name="grouping"
            :options="form.groupings"
            value-key="id"
            placeholder-key="name"
            @input-was-changed="onGroupingChanged"
            >
        </charon-select>

        <select v-model="groupingid" class="custom">
            <option v-for="grouping in form.groupings" v-bind:key="grouping.id">
                {{ grouping.name }}
  </option>
</select>

        <br />
        </div>
        <p>{{ form.fields.grouping_id }}</p>
        <p>{{ form }}</p>

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
            onGroupingChanged(grouping) {
                VueEvent.$emit('grouping-was-changed', grouping);
            }
        }
    }
</script>