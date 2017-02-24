<template>
    <div>

        <form :action="'/mod/charon/courses/' + form.course_id + '/settings'" method="post">

            <input type="hidden" name="_token" :value="csrf_token">

            <tester-settings-section
                    :form="form">
            </tester-settings-section>

            <presets-section
                    :presets="form.presets"
                    :gradingMethods="form.grading_methods"
                    :gradeTypes="form.grade_types"
                    :gradeNamePrefixes="form.grade_name_prefixes"
                    :courseId="form.course_id">
            </presets-section>

            <input type="submit" value="Save" class="btn btn-default">

        </form>

        <loader :visible="loaderVisible !== 0"></loader>

    </div>
</template>

<script>
    import TesterSettingsSection from './TesterSettingsSection.vue';
    import PresetsSection from './PresetsSection.vue';
    import Translate from '../../mixins/translate';
    import Loader from '../popup/partials/Loader.vue';

    export default {
        mixins: [ Translate ],

        props: {
            form: { required: true },
            csrf_token: { required: true },
        },

        data() {
            return {
                loaderVisible: 0
            };
        },

        components: { TesterSettingsSection, PresetsSection, Loader },

        mounted() {
            VueEvent.$on('unittests-git-was-changed', unittests_git => this.form.fields.unittests_git = unittests_git);
            VueEvent.$on('tester-type-was-changed', tester_type => this.form.fields.tester_type = tester_type);

            VueEvent.$on('show-loader', () => this.loaderVisible++);
            VueEvent.$on('hide-loader', () => this.loaderVisible--);
        },
    }
</script>
