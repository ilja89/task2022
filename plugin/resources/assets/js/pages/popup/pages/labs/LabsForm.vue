<template>
    <div>
        <lab-info-section :form="form"></lab-info-section>
        <add-multiple-labs-section :form="form"></add-multiple-labs-section>
        <div class="btn-container btn-container-left">
            <button v-on:click="saveClicked" class="btn-labs btn-save-labs">Save</button>
        </div>

        <div class="btn-container btn-container-right">
            <button v-on:click="cancelClicked" class="btn-labs btn-cancel-labs">Cancel</button>
        </div>

    </div>
</template>

<script>
    import LabInfoSection from "./sections/LabInfoSection";
    import AddMultipleLabsSection from "./sections/AddMultipleLabsSection";
    import Lab from "../../../../api/Lab";

    export default {

        components: { LabInfoSection, AddMultipleLabsSection },

        data() {
            return {
                form: {start: {time: null}, end: {time: null}, weeks: []}
            }
        },

        methods: {
            saveClicked() {
                // send info to backend
                //window.location = "popup#/labs";
                console.log(this.form)
                Lab.save('1', this.form.start.time, this.form.end.time, lab => {  // works
                    VueEvent.$emit('show-notification', 'Lab saved!')
                });
            },
            cancelClicked() {
                window.location = "popup#/labs";
            }
        }
    }
</script>