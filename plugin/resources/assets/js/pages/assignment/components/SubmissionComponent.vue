<template>
  <v-dialog v-model="isActive" width="80%" style="position: relative; z-index: 3000" @click:outside="close"
            transition="dialog-bottom-transition">

    <v-card style="background-color: white; overflow-y: auto;">
      <v-toolbar :color="color" dark>
        <span class="headline">{{ translate('submissionText') }} {{ submission.git_hash }}</span>

        <v-spacer></v-spacer>

        <v-btn color="error" @click="close">
          {{ translate('closeText') }}
        </v-btn>
      </v-toolbar>

      <v-card-text class="pt-4">
        <div v-if="hasCommitMessage">
          <h3>{{ translate('commitMessageText') }}</h3>
          <p>{{ submission.git_commit_message }}</p>
        </div>

        <h3 v-if="toggleOn">Showing table</h3>
        <h3 v-else>Showing mail</h3>

        <label class="switch">
          <input type="checkbox" v-model="toggleOn">
          <span class="slider round"></span>
        </label>

        <div v-if="hasMail && !toggleOn">
          <h3>{{ translate('testerFeedbackText') }}</h3>
          <pre v-html="submission.mail"></pre>
        </div>
        <div v-if="toggleOn">
          <submission-table :submission="submission"></submission-table>
        </div>

        <h3>{{ translate('filesText') }}</h3>

        <files-component-without-tree :submission="submission" :testerType="testerType" :isRound="true">
        </files-component-without-tree>

        <div class="review-comments">
          <h3>{{ translate('feedbackText') }}</h3>
          <review-comment-component v-if="reviewCommentCount" :files="files" view="student"></review-comment-component>
          <v-card v-else class="message">
            {{ translate('noFeedbackInfo') }}
          </v-card>
        </div>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>

<script>
import {FilesComponentWithoutTree} from '../../../components/partials'
import {Translate} from '../../../mixins'
import SubmissionTable from "./SubmissionTable";
import {File, ReviewComment} from "../../../api";
import ReviewCommentComponent from "../../../components/partials/ReviewCommentComponent";
import {mapState} from "vuex";

export default {
  name: "submission-component",

  mixins: [Translate],

  components: {
    ReviewCommentComponent, FilesComponentWithoutTree, SubmissionTable
  },

  props: {
    submission: {required: true},
    color: {required: true}
  },

  data() {
    return {
      isActive: null,
      routeChanged: null,
      testerType: '',
      toggleOn: false,
      files: [],
      reviewCommentCount: 0,
      reviewCommentIdsWithNotify: [],
    }
  },

  created() {
    this.routeChanged = true;
  },

  computed: {
    ...mapState([
      'charon_id',
      'student_id',
    ]),

    hasCommitMessage() {
      return this.submission.git_commit_message !== null && this.submission.git_commit_message.length > 0
    },

    hasMail() {
      return this.submission.mail !== null && this.submission.mail.length > 0
    },
  },

  watch: {
    $route (to, from){
      if (this.submission.id === parseInt(to.params.submission_id)) {
        this.routeChanged = true;
      }
    },
    routeChanged (newVal, val) {
      if (newVal === true) {
        this.isActive = true;
        this.routeChanged = false;
      }
    }
  },

  mounted() {
    this.testerType = window.testerType
    this.getFiles();
  },

  methods: {
    close() {
      this.$router.push('/');
    },
    getFiles() {
      File.findBySubmission(this.submission.id, files => {
        this.files = files
        this.checkComments();
      })
    },

    checkComments() {
      this.reviewCommentCount = 0;
      this.files.forEach(file => {
        if (file.review_comments.length > 0) {
          file.review_comments.forEach(reviewComment => {
            this.reviewCommentCount++;
            if (reviewComment.notify) {
              this.reviewCommentIdsWithNotify.push(reviewComment.id);
            }
          });
        }
      });
    },

    onClickSubmissionInformation() {
      this.active = true;
      if (this.reviewCommentIdsWithNotify.length) {
        ReviewComment.clearNotifications(
            this.reviewCommentIdsWithNotify, this.charon_id, this.student_id, () => {
              this.reviewCommentIdsWithNotify = [];
            });
      }
    }
  }
}
</script>
<style src="../../../../../../public/css/submissionModal.css" scoped>
@import url("https://cdn.jsdelivr.net/npm/@mdi/font@5.x/css/materialdesignicons.min.css");
@import url("https://fonts.googleapis.com/css?family=Material+Icons");
</style>
