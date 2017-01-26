<template>

    <charon-fieldset>
        <template slot="title">{{ translate('tester_settings_title') }}</template>
        <slot>

            <charon-text-input
                    input_name="unittests_git"
                    :required="true"
                    :input_label="translate('unittests_git_label')"
                    :input_value="form.fields.unittests_git"
                    @input-was-changed="onUnittestsGitChanged">
            </charon-text-input>

        </slot>
    </charon-fieldset>

</template>

<script>
    import CharonFieldset from '../form/CharonFieldset.vue';
    import CharonTextInput from '../form/CharonTextInput.vue';

    import Translate from '../../mixins/translate';

    export default {
        mixins: [ Translate ],

        components: { CharonFieldset, CharonTextInput },

        props: {
            form: { required: true }
        },

        methods: {
            onUnittestsGitChanged(unittestsGit) {
                VueEvent.$emit('unittests-git-was-changed', unittestsGit);
            }
        }
    }
</script>
