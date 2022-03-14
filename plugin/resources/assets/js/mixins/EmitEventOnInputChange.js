export default {
    methods: {
        sendTemplates(templateList) {
            VueEvent.$emit('save-templates', templateList);
        },

        onNameChanged(name) {
            VueEvent.$emit('name-was-changed', name);
        },

        onProjectFolderChanged(projectFolder) {
            VueEvent.$emit('project-folder-was-changed', projectFolder);
        },

        onExistingTaskChanged(tasks) {
            VueEvent.$emit('existing-task-was-changed', tasks);
        },

        onTesterExtraChanged(extra) {
            VueEvent.$emit('tester-extra-was-changed', extra);
        },

        onSystemExtraChanged(extra) {
            VueEvent.$emit('system-extra-was-changed', extra);
        },

        onUnittestsGitChanged(gitUrl) {
            VueEvent.$emit('unittests-git-was-changed', gitUrl);
        },

        onTesterTypeChanged(testerType) {
            VueEvent.$emit('tester-type-was-changed', testerType);
        },

        onGradingMethodChanged(gradingMethod) {
            VueEvent.$emit('grading-method-was-changed', gradingMethod);
        },

        onGradeTypeActivated(gradeTypeCode) {
            VueEvent.$emit('grade-type-was-activated', gradeTypeCode);
        },

        onGradeTypeDeactivated(gradeTypeCode) {
            VueEvent.$emit('grade-type-was-deactivated', gradeTypeCode);
        },

        onCalculationFormulaChanged(calculationFormula) {
            VueEvent.$emit('calculation-formula-was-changed', calculationFormula);
        },

        onMaxScoreChanged(maxScore) {
            VueEvent.$emit('max-score-was-changed', maxScore);
        },

        onPresetChanged(presetId) {
            VueEvent.$emit('preset-was-changed', presetId);
        },

        onPlagiarismServiceChanged(index, serviceCode) {
            VueEvent.$emit('plagiarism-service-was-changed', index, serviceCode);
        },

        onPlagiarismServiceAdded() {
            VueEvent.$emit('plagiarism-service-was-added');
        },

        onPlagiarismServiceRemoved(index) {
            VueEvent.$emit('plagiarism-service-was-removed', index);
        },

        onPlagiarismEnabledChanged(plagiarismEnabled) {
            VueEvent.$emit('plagiarism-enabled-was-changed', plagiarismEnabled.target.checked)
        },

        onPlagiarismResourceProviderAdded() {
            VueEvent.$emit('plagiarism-resource-provider-was-added')
        },

        onPlagiarismResourceProviderRepositoryChanged(index, repo) {
            VueEvent.$emit('plagiarism-resource-provider-repository-changed', index, repo)
        },

        onPlagiarismResourceProviderRemoved(index) {
            VueEvent.$emit('plagiarism-resource-provider-removed', index)
        },

        onPlagiarismExcludesChanged(excludes) {
            VueEvent.$emit('plagiarism-excludes-was-changed', excludes)
        },
    },
};
