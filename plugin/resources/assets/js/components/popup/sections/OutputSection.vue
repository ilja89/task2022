<template>

    <popup-section
            title="Email and outputs"
            subtitle="Output from the tester and mail sent to the student.">

        <div class="card">Hello World</div>

    </popup-section>
</template>

<script>
    import PopupSection from '../partials/PopupSection.vue';

    export default {
        components: { PopupSection },

        props: {
            context: { required: true }
        }
    }
</script>
