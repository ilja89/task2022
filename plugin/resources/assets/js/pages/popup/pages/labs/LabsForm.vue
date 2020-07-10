<template>
    <div>
        <lab-info-section :form="form"></lab-info-section>
        <add-multiple-labs-section :form="form"></add-multiple-labs-section>
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
        }
    }
</script>