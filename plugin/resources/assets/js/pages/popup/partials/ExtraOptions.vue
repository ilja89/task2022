<template>

    <v-menu auto>
        <template v-slot:activator="{ on: menu, attrs }">
            <v-tooltip bottom>
                <template v-slot:activator="{ on: tooltip }">
                    <v-btn
                            text block
                            v-bind="attrs"
                            v-on="{ ...tooltip, ...menu }"
                    >
                        <md-icon>more_vert</md-icon>
                    </v-btn>
                </template>
                <span>Manual submissions</span>
            </v-tooltip>
        </template>
        <v-list>

            <v-list-item :disabled="!canAddSubmission" @click="addManualSubmission">
                <v-list-item-title>Add a manual submission</v-list-item-title>
            </v-list-item>

            <v-list-item :disabled="!canRetestSubmission" @click="retestTask">
                <v-list-item-title>Retest this task</v-list-item-title>
            </v-list-item>

        </v-list>
    </v-menu>

</template>

<script>
    import {mapState} from 'vuex'
    import {mixin as clickaway} from 'vue-clickaway';
    import {Submission} from '../../../api';

    export default {

        mixins: [clickaway],

        data() {
            return {
                menuIsOpen: false,
            };
        },

        computed: {
            ...mapState([
                'charon',
                'student',
                'submission',
            ]),

            canAddSubmission() {
                return this.charon != null && this.student != null;
            },

            canRetestSubmission() {
                return !!this.submission
            },
        },

        methods: {
            onIconClicked() {
                this.menuIsOpen = !this.menuIsOpen;
            },

            addManualSubmission() {

                if (!this.canAddSubmission) {
                    return;
                }

                Submission.addNewEmpty(this.charon.id, this.student.id, submission => {
                    this.menuIsOpen = false;
                    VueEvent.$emit("refresh-page");
                });
            },

            onClickedAway() {
                this.menuIsOpen = false
            },

            retestTask() {
                if (!this.canAddSubmission) {
                    return
                }

                Submission.retest(this.submission.id, response => {
                    if (response.data.status === 200) {
                        window.VueEvent.$emit('show-notification', response.data.data.message)
                    }
                })
            },
        }
    }
</script>
