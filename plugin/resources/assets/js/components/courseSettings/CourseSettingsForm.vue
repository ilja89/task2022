<template>
    <div class="">

        <form :action="'/mod/charon/courses/' + form.course_id + '/settings'" method="post">

            <input type="hidden" name="_token" :value="csrf_token">

            <tester-settings-section
                    :form="form">
            </tester-settings-section>

            <presets-section
                    :presets="form.presets">
            </presets-section>

            <input type="submit" value="Save" class="btn btn-default">

        </form>

    </div>
</template>

<script>
    import TesterSettingsSection from './TesterSettingsSection.vue';
    import PresetsSection from './PresetsSection.vue';
    import Translate from '../../mixins/translate';

    export default {
        mixins: [ Translate ],

        props: {
            form: { required: true },
            csrf_token: { required: true }
        },

        components: { TesterSettingsSection, PresetsSection },

        mounted() {
            VueEvent.$on('unittests-git-was-changed', unittests_git => this.form.fields.unittests_git = unittests_git);
            VueEvent.$on('tester-type-was-changed', tester_type => this.form.fields.tester_type = tester_type);
        }
    }
</script>
