<template>
  <div>
    <page-title :title="page_name"></page-title>

    <popup-section title="Activities"
                   subtitle="Here are all the charon activities for this course. Choose one to see its dashboard">
      <div class="latest-submissions">
        <transition-group name="list">
          <div v-for="(activityChunk, index) in charonActivitiesChunks" :key="index" class="columns">
            <div v-for="activity in activityChunk" class="column">
              <div class="card  hover-overlay  submission" @click="activitySelected(activity)">
                <div>
                  <wbr>
                  {{ activity.name }}
                  <wbr>
                </div>
              </div>
            </div>
          </div>
        </transition-group>
      </div>
    </popup-section>
  </div>
</template>

<script>
import {mapState} from "vuex";
import {PageTitle} from '../partials'
import {PopupSection} from "../layouts";

export default {
  name: "activities-overview",

  components: {PopupSection, PageTitle},

  data() {
    return {
    }
  },

  computed: {
    ...mapState([
      'course',
      'charons'
    ]),
    version: function () { return window.appVersion; },

    charonActivitiesChunks() {
      const chunkSize = 2

      let chunks = []
      let chunk = []
      this.charons.forEach(activity => {
        if (chunk.length < chunkSize) {
          chunk.push(activity)
        } else {
          chunks.push(chunk)
          chunk = [activity]
        }
      })

      if (chunk.length) {
        chunks.push(chunk)
      }

      return chunks
    },

    page_name() {
      return window.course_name
    }
  },

  methods: {
    activitySelected(submission) {
      this.$router.push(this.submissionLink(submission.id))
    },
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

  white-space: nowrap;
  line-height: 1.5rem;

  @include touch {
    padding-bottom: 20px;
    padding-left: 10px;
  }
}

</style>