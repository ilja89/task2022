<template>
  <div v-if="codes.length > 0">
    <charon-tabs>
      <charon-tab v-for="(code, index) in codes"
                  v-bind:key="code.path"
                  :name="code.path"
                  :selected="index===0">
        <code-editor :codeId="index"
                     :language="language"
                     :codes="codes"
                     :allow_submission="allow_submission"
        ></code-editor>
      </charon-tab>
    </charon-tabs>
    <div v-if="allow_submission > 0">
      <a class="button is-link" @click="getTemplates">
        {{ translate('resetToTemplates') }}
      </a>
      <div style="float: right">
        <button v-if="submitDisabled === false" class="btn btn-primary" @click="submitClicked" :disabled="submitDisabled">
          {{ translate('submitButton') }}
        </button>
        <img v-if="submitDisabled" style="margin-right: 20px" alt="submit loading" height="32px" src="pix/refresh.png"
             v-bind:class="submitDisabled ? 'rotating' : ''"
             width="32px">
      </div>
    </div>
  </div>
</template>
<script>
import CharonTab from "../../../components/partials/CharonTab";
import CharonTabs from "../../../components/partials/CharonTabs";
import CodeEditor from "./CodeEditor";
import Submission from "../../../api/Submission";
import Translate from "../../../mixins/Translate";
import {File} from "../../../api";

export default {
  mixins: [Translate],

  name: "CodeTemplates",

  components: {CharonTab, CharonTabs, CodeEditor},

  props: {
    language: {required: true},
    allow_submission: {required: true}
  },

  beforeMount() {
    if (!(allow_submission > 0)) {
      this.getTemplates();
    } else {
      VueEvent.$on('latest-submission-to-editor', (submissionId) => {
        this.getFilesForSubmission(submissionId);
      });
      VueEvent.$on('latest-submission-does-not-exist', () => {
        this.getTemplates();
      });
    }
  },

  mounted() {
    VueEvent.$on('change-editor-content', (codes) => {
      this.codes = codes;
      VueEvent.$emit('change-editor', codes);
    });
    VueEvent.$on('reset-submit-button', () => {
      this.submitDisabled = false
    });
  },

  data() {
    return {
      submitDisabled: false,
      codes: [],
    }
  },

  methods: {
    getFilesForSubmission(submissionId) {
      try {
        File.findBySubmission(submissionId, files => {
          this.codes = files
        })
      } catch (e) {
        VueEvent.$emit('show-notification', 'Error getting templates!')
      }
    },
    getTemplates() {
      try {
        Submission.getTemplates(window.charonId, answer => {
          this.codes = answer
          VueEvent.$emit('change-editor-content', answer);
        })
      } catch (e) {
        VueEvent.$emit('show-notification', 'Error getting templates!')
      }
    },
    submitClicked() {
      VueEvent.$emit('show-notification', 'Trying to submit a new submission')
      this.submitDisabled = true;

      let sourceFiles = [];
      for (let i = 0; i < this.codes.length; i++) {
        sourceFiles.push({"path": this.codes[i].path, "content": this.codes[i].contents});

      }
      try {
        Submission.submitSubmission(sourceFiles, window.charonId, (response) => {
          if (response['message'] === 'Testing the submission was successful') {
            VueEvent.$emit('add-submission', response['submission']);
          }
          VueEvent.$emit('show-notification', response['message']);
          this.submitDisabled = false;
        }
        )
      } catch (e) {
        VueEvent.$emit('show-notification', 'Error submitting a submission!')
        this.submitDisabled = false;
      }
    }
  }
}
</script>

<style scoped>

@import '../../../../../../public/css/buttons/refreshButton.css';

</style>
