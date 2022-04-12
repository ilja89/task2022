<template>
  <popup-section title="Latest submissions"
                 subtitle="Here are the latest submissions for the student in this course">
    <div class="latest-submissions" v-if="latestSubmissions.length !== 0">
      <transition-group name="list">
        <div v-for="(submissionChunk, index) in latestSubmissionsChunks" :key="`submissionChunk-${index}`" class="columns">
          <div v-for="(submission) in submissionChunk" :key="submission.id" class="column">
            <div class="card  hover-overlay  submission" @click="submissionSelected(submission)">
              <div>
                <span class="submission-line">
                    {{ submission | submissionTime }} <span class="timestamp-separator">|</span>
                </span>
                <span class="submission-line">
                    {{ submission.name }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </transition-group>
    </div>
    <div v-else>
      <v-card-title> {{ this.empty }} </v-card-title>
    </div>
  </popup-section>

</template>

<script>
  import moment from 'moment'
  import {mapGetters, mapActions} from 'vuex'
  import {PopupSection} from '../layouts/index'

  export default {
    name: "StudentDetailsSubmissionsSection",

    components: {PopupSection},

    data() {
      return {
        empty: 'No submissions for this student!'
      }
    },

    props: {
      latestSubmissions: {
        required: true,
        default: [],
        type: Array
      }
    },

    computed: {
      ...mapGetters([
        'courseId',
        'submissionLink',
      ]),

      latestSubmissionsChunks() {
        const chunkSize = 2

        let chunks = []
        let chunk = []
        this.latestSubmissions.forEach(submission => {
          if (chunk.length < chunkSize) {
            chunk.push(submission)
          } else {
            chunks.push(chunk)
            chunk = [submission]
          }
        })

        if (chunk.length) {
          chunks.push(chunk)
        }

        return chunks
      },
    },

    filters: {
      submissionTime(submission) {
        return moment(submission.created_at).format('D MMM HH:mm')
      },
    },

    methods: {
      ...mapActions([
        'fetchStudent',
      ]),

      submissionSelected(submission) {
        this.$router.push(this.submissionLink(submission.id))
      }
    }
  }
</script>

<style lang="scss" scoped>

@import '../../../../../../../node_modules/bulma/sass/utilities/all';

.submission {
  margin-top: 0;
  margin-bottom: 0;
  padding-top: 30px;
  padding-bottom: 30px;

  word-break: break-word;
  line-height: 1.5rem;

  @include touch {
    padding-bottom: 20px;
    padding-left: 10px;
  }
}

.submission-line {
  display: inline-block;
}

.timestamp-separator {
  padding-left: 4px;
  padding-right: 4px;
}

</style>