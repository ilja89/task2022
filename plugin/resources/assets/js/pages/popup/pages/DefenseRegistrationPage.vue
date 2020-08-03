<template>
    <div>
        <page-title :title="'Defense registrations'"></page-title>
        <defense-registrations-section></defense-registrations-section>
    </div>
</template>

<script>

    import PageTitle from "../partials/PageTitle";
    import DefenseRegistrationsSection from "../sections/DefenseRegistrationsSection";

    export default {
        name: "defense-registrations-page",
        components: { PageTitle, DefenseRegistrationsSection }

    }
</script>
