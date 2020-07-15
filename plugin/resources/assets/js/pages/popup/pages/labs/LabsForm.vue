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

    export default {

        components: { LabInfoSection, AddMultipleLabsSection },

        data() {
            return {
                form: {teachers: [{name: "Ago", id: 1}, {name: "Kadri", id: 2}, {name: "Orav", id: 3}]}
            }
        },

        methods: {
            saveClicked() {
                // send info to backend
                window.location = "popup#/labs";
            },
            cancelClicked() {
                window.location = "popup#/labs";
            }
        }
    }
</script>