<template>

    <fieldset class="clearfix collapsible" id="id_modstandardelshdr">

        <legend class="ftoggler">{{ translate('grouping') }}</legend>

        <div class="fcontainer clearfix fitem">

        <p class="input-helper">{{ translate('grouping_selection_helper') }}</p>
        <select v-model="form.fields.grouping_id"
        @input-was-changed="onGroupingChanged(form.fields.grouping_id)"
        class="custom-select">
            <option v-for="grouping in form.groupings" v-bind:key="grouping.id" v-bind:value="grouping.id">
                {{ grouping.name }}
  </option>
</select>

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
            onGroupingChanged(grouping) {
                VueEvent.$emit('grouping-was-changed', grouping);
            }
        }
    }
</script>