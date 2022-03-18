<template>
    <charon-fieldset :open="sectionOpen">
        <template v-slot:title>{{ translate('plagiarism_title') }}</template>

        <slot>

            <charon-select
                name="plagiarism_language_type_code"
                :required="true"
                :options="form.plagiarism_lang_types"
                :value="form.fields.plagiarism_language_type_code"
                :label="translate('plagiarism_lang_label')"
                @input-was-changed="onPlagiarismLangTypeChanged"
            ></charon-select>

            <charon-select
                name="plagiarism_gitlab_group"
                :required="true"
                :options="form.gitlab_group_types"
                :value="form.fields.plagiarism_gitlab_group"
                :label="translate('plagiarism_gitlab_group_label')"
                @input-was-changed="onGitlabGroupChanged"
            ></charon-select>

            <charon-select
                name="gitlab_location_type_code"
                :required="true"
                :options="form.gitlab_location_types"
                :value="form.fields.gitlab_location_type_code"
                :label="translate('plagiarism_gitlab_location_label')"
                @input-was-changed="onGitlabLocationTypeChanged"
            ></charon-select>

            <charon-text-input
                name="plagiarism_file_extensions"
                input_class="is-half"
                :required="true"
                :value="form.fields.plagiarism_file_extensions"
                :label="translate('plagiarism_file_extensions_label')"
                :helper_text="translate('plagiarism_file_extensions_helper')"
                @input-was-changed="onPlagiarismFileExtensionsChanged"
            ></charon-text-input>

            <charon-text-input
                name="plagiarism_moss_passes"
                input-type="number"
                input_class="is-half"
                :required="true"
                :value="form.fields.plagiarism_moss_passes"
                :label="translate('plagiarism_moss_passes_label')"
                :helper_text="translate('plagiarism_moss_passes_helper')"
                @input-was-changed="onPlagiarismMossMatchesChanged"
            ></charon-text-input>

            <charon-text-input
                name="plagiarism_moss_matches_shown"
                input-type="number"
                input_class="is-half"
                :required="true"
                :value="form.fields.plagiarism_moss_matches_shown"
                :label="translate('plagiarism_moss_matches_shown_label')"
                :helper_text="translate('plagiarism_moss_matches_shown_helper')"
                @input-was-changed="onPlagiarismMossMatchesShownChanged"
            ></charon-text-input>
        </slot>

    </charon-fieldset>
</template>

<script>
import {CharonFieldset, CharonSelect, CharonTextInput} from "../../../components/form";
import {EmitEventOnInputChange, Translate} from "../../../mixins";

export default {
    mixins: [ Translate, EmitEventOnInputChange ],

    components: { CharonFieldset, CharonTextInput, CharonSelect },

    props: {
        form: { required: true },
        sectionOpen: {
            required: false,
            default: false
        }
    },
}
</script>

<style scoped>

</style>