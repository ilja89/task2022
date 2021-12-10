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

        <h3 v-if="toggleShowTable">{{ translate('showingTable') }}</h3>
        <h3 v-else>{{ translate('showingMail') }}</h3>

        <toggle-button @buttonClicked="showTable($event)"></toggle-button>

        <div v-if="hasMail && !toggleShowTable">
          <h3>{{ translate('testerFeedbackText') }}</h3>
          <pre v-html="submission.mail"></pre>
        </div>
        <div v-if="toggleShowTable">
          <submission-table-component :testSuites="submission['test_suites']"></submission-table-component>
        </div>

        <h3 v-if="submission.files && submission.files.length > 0">{{ translate('filesText') }}</h3>

        <files-component-without-tree v-if="submission.files && submission.files.length > 0" :submission="submission" :testerType="testerType" :isRound="true">
        </files-component-without-tree>

        <div class="review-comments">
          <div v-if="!toggleShowAllSubmissions">
            <h3>{{ translate('feedbackTextSingleSubmission') }}</h3>
          </div>
          <div v-else>
            <h3>{{ translate('feedbackTextAllSubmissions') }}</h3>
          </div>

          <toggle-button @buttonClicked="showAllSubmissions($event)"></toggle-button>

          <files-with-review-comments v-if="this.filesWithReviewComments.length > 0"
                                      view="student"
                                      :filesWithReviewComments="this.getFilesWithReviewComments()"
          ></files-with-review-comments>
          <v-card v-else class="message">
            {{ translate('noFeedbackInfo') }}
          </v-card>
        </div>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>

<script>
import {FilesComponentWithoutTree, FilesWithReviewComments, SubmissionTableComponent, ToggleButton} from '../../../components/partials';
import {Translate} from '../../../mixins'
import {ReviewComment} from "../../../api";
import {mapState} from "vuex";

export default {
  name: "submission-component",

  mixins: [Translate],

  components: {
    FilesComponentWithoutTree, FilesWithReviewComments, SubmissionTableComponent, ToggleButton
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
      toggleShowTable: false,
      reviewCommentCount: 0,
      reviewCommentIdsWithNotify: [],
      toggleShowAllSubmissions: false,
    }
  },

  created() {
    this.routeChanged = true;
  },

  computed: {
    ...mapState([
      'charon_id',
      'student_id',
      'filesWithReviewComments',
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
    this.checkNewComments();
  },

  methods: {
    close() {
      this.$router.push('/');
    },

    getFilesWithReviewComments() {
      if (this.toggleShowAllSubmissions) {
        return this.filesWithReviewComments;
      }
      let files = [];
      this.filesWithReviewComments.forEach(file => {
        if (file.submissionId === this.submission.id) {
          files.push(file);
        }
      })
      return files;
    },

    checkNewComments() {
      this.reviewCommentCount = 0;
      this.filesWithReviewComments.forEach(file => {
        if (file.submissionId === this.submission.id) {
          file.reviewComments.forEach((reviewComment) => {
            this.reviewCommentCount++;
            if (reviewComment.notify) {
              this.reviewCommentIdsWithNotify.push(reviewComment.id)
            }
          });
        }
      })
    },

    onClickSubmissionInformation() {
      this.active = true;
      if (this.reviewCommentIdsWithNotify.length) {
        ReviewComment.clearNotifications(
            this.reviewCommentIdsWithNotify, this.charon_id, this.student_id, () => {
              this.reviewCommentIdsWithNotify = [];
            });
      }
    },

    showTable(bool) {
      this.toggleShowTable = bool;
    },

    showAllSubmissions(bool) {
      this.toggleShowAllSubmissions = bool;
    },
  }
}
</script>
<style src="../../../../../../public/css/submissionModal.css" scoped>
@import url("https://cdn.jsdelivr.net/npm/@mdi/font@5.x/css/materialdesignicons.min.css");
@import url("https://fonts.googleapis.com/css?family=Material+Icons");

.review-comments {
  padding-top: 10px;
}

.signal {
  color: #f00!important;
}

</style>
