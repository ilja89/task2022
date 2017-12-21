<template>
    <popup-section
        title="Pick a task"
        subtitle="Here are the submissions from one student for the given task."
    >

        <template slot="header-right">
            <charon-select/>
        </template>

        <submissions-list/>

    </popup-section>
</template>

<script>
    import { PopupSection } from '../layouts';
    import { CharonSelect, SubmissionsList } from '../partials';

    export default {
        components: { PopupSection, CharonSelect, SubmissionsList },
    }
</script>
