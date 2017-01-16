module.exports = {
    methods: {
        onNameChanged(name) {
            VueEvent.$emit('name-was-changed', name);
        },

        onProjectFolderChanged(projectFolder) {
            VueEvent.$emit('project-folder-was-changed', projectFolder);
        },

        onTesterTypeChanged(testerType) {
            VueEvent.$emit('tester-type-was-changed', testerType);
        },

        onGradingMethodChanged(gradingMethod) {
            VueEvent.$emit('grading-method-was-changed', gradingMethod);
        }
    }
};
