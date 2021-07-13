<template>
  <div>
    <charon-tabs>
      <charon-tab v-for="(code, index) in this.codes"
                  :name="code.path"
                  :selected="index===0">
        <code-editor :codeId="index"
                     :code="code.contents"
                     :language="this.language"
                     :codes="codes"
        ></code-editor>
      </charon-tab>
    </charon-tabs>
    <v-btn class="ma-2 submitBtn" small tile outlined color="primary" @click="submitClicked">
      Submit
    </v-btn>
  </div>
</template>
<script>
import CharonTab from "../../../components/partials/CharonTab";
import CharonTabs from "../../../components/partials/CharonTabs";
import CodeEditor from "./CodeEditor";
import Submission from "../../../api/Submission";

export default {
  name: "AssignmentTab",

  components: {CharonTab, CharonTabs, CodeEditor},

  props: {
    language: {require: true}
  },

  mounted() {
    this.getTemplates();
  },

  data() {
    return {
      codes: [],
    }
  },

  methods: {
    getTemplates() {
      Submission.getTemplates(window.charonId, answer => {
        this.codes = answer
      })
    },

    submitClicked() {
      let sourceFiles = [];

      for (let i = 0; i < this.codes.length; i++) {
        sourceFiles.push({"path": this.codes[i].path, "content": this.codes[i].contents});
      }

      try {
        Submission.saveSubmission(sourceFiles, window.charonId, window.studentId, () =>
            VueEvent.$emit('show-notification', 'Submission successfully saved!')
        )
      } catch (e) {
        VueEvent.$emit('show-notification', 'Error saving submission!')
      }
    }
  }
}
</script>