<template>
  <div v-if="codes.length > 0">
    <charon-tabs>
      <charon-tab v-for="(code, index) in codes"
                  :name="code.path"
                  :selected="index===0">
        <code-editor :codeId="index"
                     :language="this.language"
                     :codes="codes"
                     :allow_submission="allow_submission"
        ></code-editor>
      </charon-tab>
    </charon-tabs>
    <a v-if="allow_submission > 0" class="button is-link" @click="submitClicked">
      Submit
    </a>
  </div>
</template>
<script>
import CharonTab from "../../../components/partials/CharonTab";
import CharonTabs from "../../../components/partials/CharonTabs";
import CodeEditor from "./CodeEditor";
import Submission from "../../../api/Submission";

export default {
  name: "CodeTemplates",

  components: {CharonTab, CharonTabs, CodeEditor},

  props: {
    language: {required: true},
    allow_submission: {required: true}
  },

  beforeMount() {
    this.getTemplates();
  },

  data() {
    return {
      codes: [],
    }
  },

  methods: {
    getTemplates() {
      try {
        Submission.getTemplates(window.charonId, answer => {
          this.codes = answer
        })
      } catch (e) {
        VueEvent.$emit('show-notification', 'Error getting templates!')
      }
    },

    submitClicked() {
      let sourceFiles = [];

      for (let i = 0; i < this.codes.length; i++) {
        sourceFiles.push({"path": this.codes[i].path, "content": this.codes[i].contents});
      }

      try {
        Submission.submitSubmission(sourceFiles, window.charonId, () =>
            VueEvent.$emit('show-notification', 'Code has been sent to tester. Please refresh submissions in a while.')
        )
      } catch (e) {
        VueEvent.$emit('show-notification', 'Error saving submission!')
      }
    }
  }
}
</script>
