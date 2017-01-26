module.exports = {
    methods: {
        onNameChanged(name) {
            VueEvent.$emit('name-was-changed', name);
        },

        onProjectFolderChanged(projectFolder) {
            VueEvent.$emit('project-folder-was-changed', projectFolder);
        },

        onExtraChanged(extra) {
            VueEvent.$emit('extra-was-changed', extra);
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
        }
    }
};
