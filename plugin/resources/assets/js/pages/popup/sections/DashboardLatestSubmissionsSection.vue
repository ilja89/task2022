<template>
  <popup-section title="Latest submissions">
    <div v-if="latestSubmissions.length" class="latest-submissions">
      <transition-group name="list">
        <div v-for="(submissionChunk, index) in latestSubmissionsChunks" :key="index" class="columns">
          <div v-for="submission in submissionChunk" class="column">
            <div class="card  hover-overlay  submission" @click="submissionSelected(submission)">
              <div>
                {{ submission | submissionTime }} <span class="timestamp-separator">|</span>
                <wbr>
                {{ submission.charon.name }} <span class="timestamp-separator">|</span>
                <wbr>
                {{ formatStudentResults(submission) }}
              </div>
            </div>
          </div>
        </div>
      </transition-group>
    </div>
    <v-card-title v-else>
      {{ empty }}
    </v-card-title>
  </popup-section>
</template>

<script>
import moment from 'moment'
import {mapGetters, mapActions} from 'vuex'
import {PopupSection} from '../layouts/index'
import {formatName} from '../helpers/formatting'

export default {
  name: "latest-submissions-section",

  components: {PopupSection},

  data() {
    return {
      empty: 'No submissions for this charon!',
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
    },

    formatStudentResults(submission) {
      return submission.users.map(user => {
        let results = submission.results
            .filter(result => result.user_id === user.id)
            .sort((a, b) => a.grade_type_code - b.grade_type_code)
            .map(result => result.calculated_result)
            .join(', ');

        return formatName(user) + ' ('  + results + ')';
      }).join(' | ');
    },
  },
}
</script>

<style lang="scss" scoped>

@import '../../../../../../../node_modules/bulma/sass/utilities/all';

.submission {
  margin-top: 0;
  margin-bottom: 0;
  padding-top: 30px;
  padding-bottom: 30px;

  white-space: nowrap;
  line-height: 1.5rem;

  @include touch {
    padding-bottom: 20px;
    padding-left: 10px;
  }
}

.timestamp-separator {
  padding-left: 4px;
  padding-right: 4px;
}

</style>