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

            <charon-text-input
                    input_class="is-half"
                    name="tester_url"
                    label="Tester asynchronous URL"
                    :value="form.fields.tester_url"
                    @input-was-changed="onTesterUrlChanged">
            </charon-text-input>

            <charon-text-input
                    input_class="is-half"
                    name="tester_sync_url"
                    label="Tester synchronous URL"
                    :value="form.fields.tester_sync_url"
                    @input-was-changed="onTesterSyncUrlChanged">
            </charon-text-input>

            <charon-text-input
                    input_class="is-half"
                    name="tester_token"
                    label="Tester token"
                    :value="form.fields.tester_token"
                    :helper_text="translate('tester_token_helper')"
                    @input-was-changed="onTesterTokenChanged">
            </charon-text-input>

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
            onTesterSyncUrlChanged(testerUrl) {
              VueEvent.$emit('tester-sync-url-was-changed', testerUrl);
            },
            onUnittestsGitChanged(unittestsGit) {
                VueEvent.$emit('unittests-git-was-changed', unittestsGit);
            },

            onTesterTypeChanged(testerType) {
                VueEvent.$emit('tester-type-was-changed', testerType);
            },

            onTesterUrlChanged(testerUrl) {
                VueEvent.$emit('tester-url-was-changed', testerUrl);
            },

            onTesterTokenChanged(testerToken) {
                VueEvent.$emit('tester-token-was-changed', testerToken);
            }
        }
    }
</script>
