<template>

    <div
        class="card  hover-overlay  submission"
        :class="{ 'confirmed-submission': submission.confirmed === 1 }"
        @click="$emit('submission-was-selected')"
    >
        <v-badge :value="submission.review_comments.length"
                 :content="submission.review_comments.length < 10 ? submission.review_comments.length : '9+'"
                 overlap
                 left
                 offset-x="-1"
        ><div class="submission-str">{{ submissionString }}</div></v-badge>

        <div class="submission-timestamps">
            <span class='timestamp-info'>Git: </span>{{ this.submission.git_timestamp }}
            <wbr>
            <span class="timestamp-separator"> | </span>
            <wbr>
            <span class='timestamp-info'>Moodle: </span>{{ this.submission.created_at }}
        </div>

        <span class="confirmed-check">
            <div v-if="submission.confirmed === 1" class="confirmed-check-check"></div>
        </span>
    </div>
</template>

<script>
    import { formatSubmissionResults } from '../helpers/formatting'

    export default {
        props: {
            /**
             * @type {{
             *   results: {calculated_result: String}[],
             *   git_timestamp: {date: String},
             *   created_at: {date: String},
             *   confirmed: Number,
             * }}
             */
            submission: {
                required: true,
                type: Object,
            },
        },

        computed: {
            submissionString() {
                return formatSubmissionResults(this.submission)
            }
        },
    }
</script>
