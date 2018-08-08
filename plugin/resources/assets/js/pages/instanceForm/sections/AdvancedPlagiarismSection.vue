<template>
    <div>
        <!--
        TODO: Have just the select inputs in the loop and inside felement so
        that they are collapsed
        -->
        <div
            v-for="(plagiarism_service, index) in form.fields.plagiarism_services"
            :key="index"
        >
            <charon-select
                :label="translate('plagiarism_service_label')"
                :name="`plagiarism_services[${index}]`"
                :options="form.plagiarism_services"
                :value="plagiarism_service"
                :helper_text="translate('plagiarism_service_helper')"
                @input-was-changed="onPlagiarismServiceChanged(index, $event)">
            </charon-select>

            <button type="button" @click="onPlagiarismServiceRemoved(index)">
                {{ translate('remove') }}
            </button>
        </div>

        <button type="button" @click="onPlagiarismServiceAdded">{{ translate('add') }}</button>
    </div>
</template>

<script>
    import { CharonSelect } from '../../../components/form'
    import { Translate, EmitEventOnInputChange } from '../../../mixins'

    export default {
        name: 'advanced-plagiarism-section',

        mixins: [ Translate, EmitEventOnInputChange ],

        components: { CharonSelect },

        props: {
            form: { required: true },
        },
    }
</script>

<style lang="scss" scoped>

</style>