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

        <div class="options-menu" :class="{ 'is-active': menu_is_open }">
            <ul @click="addManualSubmission">
                <li :class="{ disabled: !canAddSubmission }">Add a manual submission</li>
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
        },

        data() {
            return {
                menu_is_open: false,
            };
        },

        computed: {
            canAddSubmission() {
                return this.charon !== null && this.student !== null;
            }
        },

        methods: {
            onIconClicked() {
                this.menu_is_open = !this.menu_is_open;
            },

            addManualSubmission() {

                if (!this.canAddSubmission) {
                    return;
                }

                Submission.addNewEmpty(this.charon.id, this.student.id, submission => {
                    this.$emit('submission-was-added');
                    this.menu_is_open = false;
                });
            },

            onClickedAway() {
                this.menu_is_open = false;
            }
        }
    }
</script>
