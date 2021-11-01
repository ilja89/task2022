<template>
  <popup-section title="Latest submissions">
    <div v-if="latestSubmissions.length" class="latest-submissions">
      <transition-group name="list">
        <div v-for="submissionChunk in submissionChunks(latestSubmissions)" :key="submissionChunk.id" class="columns">
          <div v-for="submission in submissionChunk.subs" class="column">
            <div class="card  hover-overlay  submission" @click="submissionSelected(submission)">
              <div>
                {{ submission | submissionTime }} <span class="timestamp-separator">|</span>
                <wbr>
                {{ submission.charon.name }} <span class="timestamp-separator">|</span>
                <wbr>
                {{ formatResults(submission) }}
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
import {latestSubmissionsChunks, formatStudentResults} from "../helpers/helpers";

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
      'submissionLink',
    ]),
  },

  filters: {
    submissionTime(submission) {
      return moment(submission.created_at).format('D MMM HH:mm')
    },
  },

    methods: {
        submissionSelected(submission) {
          this.$router.push(this.submissionLink(submission.id))
        },

        submissionChunks(latestSubmissions) {
            return latestSubmissionsChunks(latestSubmissions)
        },

        formatResults(submission) {
            return formatStudentResults(submission)
        }
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