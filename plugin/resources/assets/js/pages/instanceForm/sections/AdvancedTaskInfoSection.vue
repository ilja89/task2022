<template>
    <div>
        <charon-text-input
                name="name"
                :label="translate('task_name_label')"
                :required="true"
                :value="form.fields.name"
                :helper_text="translate('task_name_helper')"
                @input-was-changed="onNameChanged">
        </charon-text-input>

        <charon-text-input
                name="project_folder"
                input_class="is-half"
                :label="translate('project_folder_name_label')"
                :required="true"
                :value="form.fields.project_folder"
                :helper_text="translate('project_folder_name_helper')"
                @input-was-changed="onProjectFolderChanged">
        </charon-text-input>

        <charon-text-input
                name="tester_extra"
                :label="translate('tester_extra_label')"
                :required="false"
                :value="form.fields.tester_extra"
                :helper_text="translate('tester_extra_helper')"
                :autocomplete="false"
                @input-was-changed="onTesterExtraChanged">
        </charon-text-input>

        <charon-text-input
            name="unittests_git"
            :label="translate('unittests_git_label')"
            :required="false"
            :value="form.fields.unittests_git"
            :helper_text="translate('unittests_git_helper')"
            :autocomplete="false"
            @input-was-changed="onUnittestsGitChanged">
        </charon-text-input>

        <charon-select
                :label="translate('tester_type_label')"
                name="tester_type_code"
                :options="form.tester_types"
                :value="form.fields.tester_type === undefined ? form.fields.tester_type_code : form.fields.tester_type"
                :helper_text="translate('tester_type_helper')"
                @input-was-changed="onTesterTypeChanged">
        </charon-select>

    </div>
</template>

<script>
    import { CharonTextInput, CharonSelect } from '../../../components/form';
    import { Translate, EmitEventOnInputChange } from '../../../mixins';

    export default {
        mixins: [ Translate, EmitEventOnInputChange ],

        components: { CharonTextInput, CharonSelect },

        props: [ 'form' ]
    }
</script>
