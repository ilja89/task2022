<template>
    <div>
        <label class="checkbox">
            <input
                type="checkbox"
                name="plagiarism_enabled"
                :checked="form.fields.plagiarism_enabled"
                value="1"
                @click="onPlagiarismEnabledChanged"
            >
            {{ translate('plagiarism_enabled') }}
        </label>

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

            <button
                class="btn btn-primary"
                type="button"
                @click="onPlagiarismServiceRemoved(index)"
            >
                {{ translate('remove') }}
            </button>
        </div>

        <button
            class="btn btn-primary"
            type="button"
            @click="onPlagiarismServiceAdded"
        >
            {{ translate('add') }}
        </button>

        <div
            v-for="(resource_provider, index) in form.fields.plagiarism_resource_providers"
            :key="`resource_providers_${index}`"
        >
            <charon-text-input
                :name="`resource_providers[${index}][repository]`"
                :value="resource_provider.repository"
                :label="translate('plagiarism_resource_provider_repository')"
                :helper_text="translate('plagiarism_resource_provider_repository_helper')"
                @input-was-changed="onPlagiarismResourceProviderRepositoryChanged(index, $event)"
            >
            </charon-text-input>

            <charon-text-area
                :name="`resource_providers[${index}][private_key]`"
                :value="resource_provider.private_key"
                :label="translate('plagiarism_resource_provider_private_key')"
                :helper_text="translate('plagiarism_resource_provider_private_key_helper')"
                @input-was-changed="form.fields.plagiarism_resource_providers[index].private_key = $event"
            ></charon-text-area>

            <button
                class="btn btn-primary"
                type="button"
                @click="onPlagiarismResourceProviderRemoved"
            >
                {{ translate('remove') }}
            </button>
        </div>

        <button
            class="btn btn-primary"
            type="button"
            @click="onPlagiarismResourceProviderAdded"
        >
            {{ translate('add') }}
        </button>

        <!-- TODO: Is this includes correct? -->

        <charon-text-input
            name="plagiarism_includes"
            :value="form.fields.plagiarism_includes"
            :label="translate('plagiarism_includes')"
            :helper_text="translate('plagiarism_includes_helper')"
            @input-was-changed="form.fields.plagiarism_includes = $event"
        >
        </charon-text-input>
    </div>
</template>

<script>
    import { CharonSelect, CharonTextInput, CharonTextArea } from '../../../components/form'
    import { Translate, EmitEventOnInputChange  } from '../../../mixins'

    export default {
        name: 'advanced-plagiarism-section',

        mixins: [ Translate, EmitEventOnInputChange ],

        components: { CharonSelect, CharonTextInput, CharonTextArea },

        props: {
            form: { required: true },
        },
    }
</script>
