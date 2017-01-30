<template>

    <popup-section
            :title="context.active_charon.name"
            subtitle="Grade the students submission">

        <template slot="header-right">
            <button class="button is-primary  save-submission-btn" @click="saveSubmission">
                Save
            </button>
        </template>

        <div class="columns is-gapless  submission-overview-container">
            <div class="column is-one-third card">Hello World Here is some stuff Tatas mori, tanquam castus poeta.</div>
            <div class="column is-7 card" v-if="context.active_submission !== null">
                <div class="result" :class="{ 'bottom-border-separator': index !== context.active_submission.results.length - 1 }"
                     v-for="(result, index) in context.active_submission.results">
                    <div>
                        {{ getGrademapByResult(result).name }} <span class="grademax">/ {{ getGrademapByResult(result).grade_item.grademax }}p</span>
                    </div>
                    <div>
                        <input type="number" step="0.01" v-model="result.calculated_result"
                                class="has-text-centered">
                    </div>
                </div>
            </div>
        </div>

    </popup-section>

</template>

<script>
    import PopupSection from '../partials/PopupSection.vue';

    export default {
        components: { PopupSection },

        props: {
            context: { required: true }
        },

        methods: {
            getGrademapByResult(result) {
                let correctGrademap = null;
                this.context.active_charon.grademaps.forEach((grademap) => {
                    if (result.grade_type_code == grademap.grade_type_code) {
                        correctGrademap = grademap;
                    }
                });

                return correctGrademap;
            },

            saveSubmission() {
                alert("SAVING SUBMISSION");
                console.log("SAVING");
                console.log(this.context.active_submission);
            }
        }
    }
</script>
