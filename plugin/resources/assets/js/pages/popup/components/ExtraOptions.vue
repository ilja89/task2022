<template>
    <div v-on-clickaway="onClickedAway">

        <div class="header-icon-container" @click="onIconClicked">
            <svg viewBox="0 0 343 343" version="1.1" xmlns="http://www.w3.org/2000/svg"  xml:space="preserve" style="fill-rule:evenodd;clip-rule:evenodd;stroke-linejoin:round;stroke-miterlimit:1.41421;">
                <g>
                    <g transform="matrix(0.823871,0,0,0.823871,0,30.1517)"><path d="M45.225,125.972C20.284,125.972 0,146.256 0,171.191C0,196.131 20.284,216.41 45.225,216.41C70.151,216.41 90.444,196.132 90.444,171.191C90.444,146.256 70.151,125.972 45.225,125.972Z" style="fill-rule:nonzero;"/></g>
                    <g transform="matrix(0.823871,0,0,0.823871,133.934,30.1517)"><path d="M45.225,125.972C20.284,125.972 0,146.256 0,171.191C0,196.131 20.284,216.41 45.225,216.41C70.151,216.41 90.444,196.132 90.444,171.191C90.444,146.256 70.151,125.972 45.225,125.972Z" style="fill-rule:nonzero;"/></g>
                    <g transform="matrix(0.823871,0,0,0.823871,267.868,30.1517)"><path d="M45.225,125.972C20.284,125.972 0,146.256 0,171.191C0,196.131 20.284,216.41 45.225,216.41C70.151,216.41 90.444,196.132 90.444,171.191C90.444,146.256 70.151,125.972 45.225,125.972Z" style="fill-rule:nonzero;"/></g>
                </g>
            </svg>
        </div>

        <div class="options-menu" :class="{ 'is-active': menuIsOpen }">
            <ul>
                <li :class="{ disabled: !canAddSubmission }" @click="addManualSubmission">
                    Add a manual submission
                </li>
                <li :class="{ disabled: !canRetestSubmission }" @click="retestTask">
                    Retest this task
                </li>
            </ul>
        </div>
    </div>
</template>

<script>
    import { mixin as clickaway } from 'vue-clickaway';
    import { Submission } from '../../../models';

    export default {

        mixins: [ clickaway ],

        props: {
            charon: { required: true },
            student: { required: true },
            submission: { required: true },
        },

        data() {
            return {
                menuIsOpen: false,
            };
        },

        computed: {
            canAddSubmission() {
                return this.charon !== null && this.student !== null;
            },

            canRetestSubmission() {
                return !! this.submission
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
                    this.$emit('submission-was-added');
                    this.menuIsOpen = false;
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
