<template>

    <fieldset class="clearfix collapsible" id="id_modstandardelshdr_DS">

        <legend class="ftoggler">{{ translate('plagiarism_title') }}</legend>

        <div class="fcontainer clearfix fitem">

            <div class="mb-6">
                <input
                    type="checkbox"
                    name="plagiarism_create_update_charon"
                    v-model="form.plagiarism_create_update_charon"
                    :value="form.plagiarism_create_update_charon"
                    :disabled="!form.fields.plagiarism_connection"
                />
                {{ checkboxText }}
            </div>

            <charon-text-input
                name="assignment_file_extensions"
                input_class="is-half"
                :label="translate('plagiarism_file_extensions_label')"
                :required="true"
                :value="form.fields.assignment_file_extensions"
                :helper_text="translate('plagiarism_file_extensions_helper')"
                :input-disabled="!form.plagiarism_create_update_charon"
                @input-was-changed="onAssignmentFileExtensionsChanged">
            </charon-text-input>

            <charon-text-input
                name="assignment_moss_passes"
                input_class="is-half"
                input-type="number"
                :label="translate('plagiarism_moss_passes_label')"
                :required="true"
                :value="form.fields.assignment_moss_passes"
                :helper_text="translate('plagiarism_moss_passes_helper')"
                :input-disabled="!form.plagiarism_create_update_charon"
                :min-value="1"
                :max-value="32767"
                @input-was-changed="onAssignmentMossPassesChanged">
            </charon-text-input>

            <charon-text-input
                name="assignment_moss_matches_shown"
                input_class="is-half"
                input-type="number"
                :label="translate('plagiarism_moss_matches_shown_label')"
                :required="true"
                :value="form.fields.assignment_moss_matches_shown"
                :helper_text="translate('plagiarism_moss_matches_shown_helper')"
                :input-disabled="!form.plagiarism_create_update_charon"
                :min-value="1"
                :max-value="50"
                @input-was-changed="onAssignmentMatchesShownChanged">
            </charon-text-input>

        </div>

    </fieldset>

</template>

<script>
import {Translate, EmitEventOnInputChange} from "../../../mixins";
import {CharonTextInput} from '../../../components/form';

export default {
    mixins: [Translate, EmitEventOnInputChange],

    components: {CharonTextInput},

    props: {
        form: {required: true}
    },

    computed: {
        checkboxText() {
            if (this.form.fields.plagiarism_connection) {
                if (this.form.fields.assignment_exists) {
                    return this.translate('plagiarism_update_charon')
                }
                return this.translate('plagiarism_create_charon')
            }
            return this.translate('plagiarism_no_connection')
        }
    }
}
</script>
