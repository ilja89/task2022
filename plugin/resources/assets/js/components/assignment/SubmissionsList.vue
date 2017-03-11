<template>
    <div>

        <h2 class="title">Submissions</h2>

        <ul class="submissions-list">
            <template v-for="submission in submissions">
                <li class="submission-row" :class="{ active: showingAdvanced(submission.id) }" @click="toggleAdvanced(submission.id)">

                    <span class="tag is-info">
                        {{ submissionString(submission) }}
                    </span>

                    <span class="submission-time">
                        {{ submission.created_at | date }}
                    </span>

                    <span class="dropdown-arrow">
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M7 10l5 5 5-5z"/><path d="M0 0h24v24H0z" fill="none"/></svg>
                    </span>
                </li>

                <div v-if="showingAdvanced(submission.id)" class="results-list-container">
                    <ul class="results-list">
                        <li v-for="result in submission.results" class="result-row">
                        <span>
                            {{ getGrademapByResult(result).name }}
                        </span>
                            <span>
                            {{ result.calculated_result }} | {{ getGrademapByResult(result).grade_item.grademax | withoutTrailingZeroes }}
                        </span>
                        </li>
                    </ul>
                </div>

            </template>
        </ul>
    </div>
</template>

<script>
    export default {
        props: {
            submissions: { required: true },
            grademaps: { required: true }
        },

        data() {
            return {
                advanced: []
            };
        },

        filters: {
            withoutTrailingZeroes(number) {
                return number.replace(/000$/, '');
            },

            date(date) {
                return window.moment(date, "YYYY-MM-DD HH:mm:ss").format("DD/MM HH:mm");
            }
        },

        methods: {
            getGrademapByResult(result) {
                let correctGrademap = null;
                this.grademaps.forEach(grademap => {
                    if (grademap.grade_type_code == result.grade_type_code) {
                        correctGrademap = grademap;
                    }
                });
                return correctGrademap;
            },

            toggleAdvanced(submissionId) {
                if (this.advanced.includes(submissionId)) {
                    let index = this.advanced.indexOf(submissionId);
                    this.advanced.splice(index, 1);
                } else {
                    this.advanced.push(submissionId);
                }
            },

            showingAdvanced(submissionId) {
                return this.advanced.includes(submissionId);
            },

            submissionString(submission) {
                let resultStr = '';
                let prefix = '';

                submission.results.forEach((result) => {
                    resultStr += prefix;
                    resultStr += result.calculated_result;
                    prefix = ' | ';
                });

                return resultStr;
            }
        }
    }
</script>
