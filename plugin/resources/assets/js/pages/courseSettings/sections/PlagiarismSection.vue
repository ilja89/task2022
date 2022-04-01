<template>
    <charon-fieldset :open="sectionOpen">
        <template v-slot:title>{{ translate('plagiarism_title') }}</template>

        <slot>

            <div>
                {{ plagiarismCourseStatus }}
            </div>

            <charon-select
                name="plagiarism_lang_type"
                :required="true"
                :options="form.plagiarism_languages"
                :value="form.fields.plagiarism_language"
                :disabled="!form.fields.plagiarism_connection"
                :label="translate('plagiarism_lang_label')"
                @input-was-changed="onPlagiarismLangTypeChanged"
            ></charon-select>

            <charon-select
                name="plagiarism_gitlab_group"
                :required="true"
                :options="form.plagiarism_gitlab_groups"
                :value="form.fields.plagiarism_gitlab_group"
                :disabled="!form.fields.plagiarism_connection"
                :label="translate('plagiarism_gitlab_group_label')"
                @input-was-changed="onGitlabGroupChanged"
            ></charon-select>

            <charon-select
                name="gitlab_location_type"
                :required="true"
                :options="form.plagiarism_project_locations"
                :value="form.fields.plagiarism_project_location"
                :disabled="!form.fields.plagiarism_connection"
                :label="translate('plagiarism_gitlab_location_label')"
                @input-was-changed="onGitlabLocationTypeChanged"
            ></charon-select>

            <charon-text-input
                name="plagiarism_file_extensions"
                input_class="is-half"
                :required="true"
                :value="form.fields.plagiarism_file_extensions"
                :input-disabled="!form.fields.plagiarism_connection"
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
                :input-disabled="!form.fields.plagiarism_connection"
                :label="translate('plagiarism_moss_passes_label')"
                :helper_text="translate('plagiarism_moss_passes_helper')"
                :min-value="1"
                :max-value="32767"
                @input-was-changed="onPlagiarismMossMatchesChanged"
            ></charon-text-input>

            <charon-text-input
                name="plagiarism_moss_matches_shown"
                input-type="number"
                input_class="is-half"
                :required="true"
                :value="form.fields.plagiarism_moss_matches_shown"
                :input-disabled="!form.fields.plagiarism_connection"
                :label="translate('plagiarism_moss_matches_shown_label')"
                :helper_text="translate('plagiarism_moss_matches_shown_helper')"
                :min-value="1"
                :max-value="50"
                @input-was-changed="onPlagiarismMossMatchesShownChanged"
            ></charon-text-input>
        </slot>

    </charon-fieldset>
</template>

<script>
import {CharonFieldset, CharonSelect, CharonTextInput} from "../../../components/form";
import {EmitEventOnInputChange, Translate} from "../../../mixins";

export default {
    mixins: [Translate, EmitEventOnInputChange],

    components: {CharonFieldset, CharonTextInput, CharonSelect},

    props: {
        form: {required: true},
        sectionOpen: {
            required: false,
            default: false
        }
    },

    computed: {
        plagiarismCourseStatus() {
            if (this.form.fields.plagiarism_connection) {
                if (this.form.fields.plagiarism_course_exists) {
                    return this.translate('plagiarism_update_course')
                }
                return this.translate('plagiarism_create_course')
            }
            return this.translate('plagiarism_no_connection')
        }
    }
}
</script>
