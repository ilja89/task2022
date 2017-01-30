<template>
    <div class="card  hover-overlay  submission" :class="{ 'confirmed-submission': submission.confirmed === 1 }">
        <div class="submission-str">{{ submissionString }}</div>
        <div class="submission-timestamps">
            <span class='timestamp-info'>Git: </span>{{ gitTimestamp }}
            <span class="timestamp-separator"> | </span>
            <span class='timestamp-info'>Moodle: </span>{{ moodleTimestamp }}
        </div>
        <span class="confirmed-check"><div class="confirmed-check-check" v-if="submission.confirmed === 1"></div></span>
    </div>
</template>

<script>
    export default {
        props: {
            submission: { required: true }
        },

        computed: {
            submissionString() {
                let resultStr = '';
                let prefix = '';
                this.submission.results.forEach((result) => {
                    resultStr += prefix;
                    resultStr += result.calculated_result;
                    prefix = ' | ';
                });

                return resultStr;
            },

            gitTimestamp() {
                return this.submission.git_timestamp.date.replace(/\.000+/, "");
            },

            moodleTimestamp() {
                return this.submission.created_at;
            }
        }
    }
</script>
