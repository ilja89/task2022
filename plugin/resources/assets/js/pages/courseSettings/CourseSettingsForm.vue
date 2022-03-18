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
                :gradeNamePrefixes="form.grade_name_prefixes"
                :courseId="form.course_id">
            </presets-section>

            <plagiarism-section
                :form="form"
            ></plagiarism-section>

            <input type="submit" value="Save" class="btn btn-default">

        </form>

        <loader :visible="loaderVisible !== 0"></loader>
    </div>
</template>

<script>
import {TesterSettingsSection, PresetsSection, PlagiarismSection} from './sections';
import {Translate} from '../../mixins';
import Loader from '../popup/partials/Loader.vue';

export default {
    mixins: [Translate],

    props: {
        form: {required: true},
        csrf_token: {required: true},
    },

    data() {
        return {
            loaderVisible: 0,
        };
    },

    components: {PlagiarismSection, TesterSettingsSection, PresetsSection, Loader},

    mounted() {
        VueEvent.$on('unittests-git-was-changed', unittests_git => this.form.fields.unittests_git = unittests_git);
        VueEvent.$on('tester-type-was-changed', tester_type => this.form.fields.tester_type = tester_type);
        VueEvent.$on('tester-url-was-changed', tester_url => this.form.fields.tester_url = tester_url);
        VueEvent.$on('tester-sync-url-was-changed', tester_url => this.form.fields.tester_sync_url = tester_url);
        VueEvent.$on('tester-token-was-changed', tester_token => this.form.fields.tester_token = tester_token);
        VueEvent.$on('plagiarism-lang-type-was-changed', langType => this.form.fields.plagiarism_lang_type_code = langType);
        VueEvent.$on('gitlab-group-was-changed', gitlabGroup => this.form.fields.plagiarism_gitlab_group = gitlabGroup);
        VueEvent.$on('gitlab-location-type-was-changed', locationType => this.form.fields.gitlab_location_type_code = locationType);
        VueEvent.$on('plagiarism-file-extension-was-changed', fileExtension => this.form.fields.plagiarism_file_extensions = fileExtension);
        VueEvent.$on('plagiarism-moss-matches-was-changed', mossMatches => this.form.fields.plagiarism_moss_passes = mossMatches);
        VueEvent.$on('plagiarism-moss-matches-shown-was-changed', matchesShown => this.form.fields.plagiarism_moss_matches_shown = matchesShown);

        VueEvent.$on('show-loader', () => this.loaderVisible++);
        VueEvent.$on('hide-loader', () => this.loaderVisible--);
    },
}
</script>
