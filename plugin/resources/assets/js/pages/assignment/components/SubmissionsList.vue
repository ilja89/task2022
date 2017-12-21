<template>
    <div>

        <h2 class="title">{{ translate('submissionsText') }}</h2>

        <ul class="submissions-list">
            <template v-for="submission in submissions">
                <li class="submission-row" :class="{ active: showingAdvanced(submission.id) }"
                    @click="toggleAdvanced(submission.id)">

                    <span class="tag is-info">
                        {{ submissionString(submission) }}
                    </span>

                    <span class="submission-time">
                        {{ submission.git_timestamp.date | date }}
                    </span>

                    <span class="dropdown-arrow">
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M7 10l5 5 5-5z"/><path
                                d="M0 0h24v24H0z" fill="none"/></svg>
                    </span>

                    <span class="open-modal-btn" @click.stop="$emit('submission-was-activated', submission)">
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M0 0h24v24H0z" fill="none"/>
                            <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                        </svg>
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

        <div v-if="canLoadMore" class="has-text-centered">
            <button class="button is-primary load-more-button" @click="loadMoreSubmissions()">
                Load more
            </button>
        </div>

    </div>
</template>

<script>
    import { Translate } from '../../../mixins';
    import { Submission } from '../../../api';

    export default {
        mixins: [ Translate ],

        props: {
            grademaps: { required: true },
            charon_id: { required: true },
            student_id: { required: true },
        },

        data() {
            return {
                advanced: [],
                submissions: [],
                canLoadMore: true
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
            },

            refreshSubmissions() {
                Submission.findByUserCharon(this.student_id, this.charon_id, (submissions) => {
                    this.submissions = submissions;
                    this.canLoadMore = Submission.canLoadMore();
                });
            },

            loadMoreSubmissions() {
                if (Submission.canLoadMore()) {
                    Submission.getNext(submissions => {
                        submissions.forEach(submission => this.submissions.push(submission));
                        this.canLoadMore = Submission.canLoadMore();
                    });
                } else {
                    this.canLoadMore = false;
                }
            },
        },

        mounted() {
            this.refreshSubmissions();
        }
    }
</script>
