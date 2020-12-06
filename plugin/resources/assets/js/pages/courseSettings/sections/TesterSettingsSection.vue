<template>

    <charon-fieldset>
		
        <template slot="title">{{ translate('tester_settings_title') }}</template>
        <slot>
            <charon-text-input
                    input_class="is-half"
                    name="unittests_git"
                    :required="true"
                    :label="translate('unittests_git_label')"
                    :value="form.fields.unittests_git"
                    :helper_text="translate('unittests_git_helper')"
                    @input-was-changed="onUnittestsGitChanged">
            </charon-text-input>

            <charon-select
                    :label="translate('tester_type_label')"
                    name="tester_type_code"
                    :options="form.tester_types"
                    :value="form.fields.tester_type_code"
                    :helper_text="translate('tester_type_helper')"
                    @input-was-changed="onTesterTypeChanged">
            </charon-select>
        </slot>
    </charon-fieldset>

</template>

<script>

    import { CharonFieldset, CharonTextInput, CharonSelect } from '../../../components/form';
    import { Translate } from '../../../mixins';

    export default {
        mixins: [ Translate ],

        components: { CharonFieldset, CharonTextInput, CharonSelect },

        props: {
            form: { required: true }
        },

        methods: {
            onUnittestsGitChanged(unittestsGit) {
                VueEvent.$emit('unittests-git-was-changed', unittestsGit);
            },

            onTesterTypeChanged(testerType) {
                VueEvent.$emit('tester-type-was-changed', testerType);
            }
        }
    }
</script>
