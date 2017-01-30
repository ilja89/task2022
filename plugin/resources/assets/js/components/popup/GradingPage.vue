<template>
    <div class="page-container">
        <page-title
                :student="context.active_student">
        </page-title>

        <popup-section
                title="Pick a task"
                subtitle="Here are the submissions from one student for the given task.">

            <template slot="header-right">
                <charon-select
                        :charons="context.charons">
                </charon-select>
            </template>

            <div class="submissions">

                <submission v-for="submission in context.submissions" :submission="submission"></submission>

                <div v-show="context.submissions.length === 0">
                    <h3 class="title is-3">No submissions found!</h3>
                </div>

            </div>

        </popup-section>

    </div>
</template>

<script>
    import PageTitle from './partials/PageTitle.vue';
    import PopupSection from './partials/PopupSection.vue';
    import CharonSelect from './partials/CharonSelect.vue';
    import Submission from './partials/Submission.vue';

    export default {
        components: { PageTitle, PopupSection, CharonSelect, Submission },

        props: {
            context: { required: true }
        }
    }
</script>
