<template>
  <div>
    <charon-tabs>
      <charon-tab v-for="(code, index) in codes"
                  :name="code"
                  :selected="index===0">
        <code-editor :codeId="index"
                     :code="code"
                     :language="language"
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

  data() {
    return {
      codes: ["def", "def2", "def3"]
    }
  },

  methods: {

    submitClicked() {
      let sourceFiles = [];

      for (let i = 0; i < this.codes.length; i++) {
        sourceFiles.push({"path": "EX03", "content": this.codes[i]});
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