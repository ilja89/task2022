<template>
  <div>
    <page-title :title="page_name"></page-title>

    <popup-section title="Activities"
                   subtitle="Here are all the charon activities for this course. Choose one to see its dashboard">
      <div class="latest-submissions">
        <transition-group name="list">
          <div v-for="activityChunk in charonActivitiesChunks" :key="activityChunk.id" class="columns">
            <div v-for="activity in activityChunk.charons" :key="activity.id" class="column">
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
import {mapGetters, mapState} from "vuex";
import {PageTitle} from '../partials'
import {PopupSection} from "../layouts";

export default {
  name: "activities-overview",

  components: {PopupSection, PageTitle},

  data() {
    return {
    }
  },

  metaInfo() {
    return {
      title: `${'Charon activity overview - ' + window.course_name}`
    }
  },

  computed: {
    ...mapGetters([
      'activityLink',
    ]),

    ...mapState([
      'course',
      'charons'
    ]),
    version: function () { return window.appVersion; },

    charonActivitiesChunks() {
      const chunkSize = 2
      let chunkIndex = 0

      let chunks = []
      let chunk = {
        id: chunkIndex,
        charons: []
      }
      this.charons.forEach(charon => {
        if (chunk.charons.length < chunkSize) {
          chunk.charons.push(charon)
        } else {
          chunkIndex++
          chunks.push(chunk)
          chunk = {
            id: chunkIndex,
            charons: [charon]
          }
        }
      })

      if (chunk.charons.length) {
        chunks.push(chunk)
      }

      return chunks
    },

    page_name() {
      return 'Charon activity overview - ' + window.course_name
    },
  },

  methods: {
    activitySelected(activity) {
      this.$router.push(this.activityLink(activity.id))

      window.location.reload();
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