<template>
  <div class="editorDiv" id="app" v-if="editor_set">

    <span>Language: {{language}}</span>

    <AceEditor
        class="editor"
        v-model="content"
        @init="editorInit"
        :lang="lang"
        theme="monk"
        width="100%"
        height="500px"
        :options="{
        enableBasicAutocompletion: true,
        enableLiveAutocompletion: true,
        fontSize: 14,
        highlightActiveLine: true,
        enableSnippets: true,
        showLineNumbers: true,
        tabSize: 2,
        showPrintMargin: false,
        showGutter: true,
        }"
        :commands="[
        {
            name: 'save',
            bindKey: { win: 'Ctrl-s', mac: 'Command-s' },
            exec: dataSubmit,
            readOnly: true,
        },
    ]"
    />

    <v-btn class="ma-2 submitBtn" small tile outlined color="primary" @click="submitClicked">
      Submit
    </v-btn>

  </div>
</template>

<script>
import Submission from "../../../api/Submission";
import AceEditor from 'vuejs-ace-editor';

export default {
  name: "App",

  components: {
    AceEditor
  },

  props: {
    language: { required: true },
    editor_set: { required: true }
  },

  data() {
    return {
      content: '',
      lang: this.language,
    }
  },

  beforeMount() {
    if (this.language === 'javang') {
      this.lang = 'java';
    }
  },

  methods: {

    submitClicked() {
      let sourceFiles = [{"path": "EX03", "content": this.content}];
      try {
        Submission.saveSubmission(sourceFiles, window.charonId, window.studentId, () =>
            VueEvent.$emit('show-notification', 'Submission successfully saved!')
        )
      } catch (e) {
        VueEvent.$emit('show-notification', 'Error saving submission!')
      }
    },

    dataSubmit() {
      //code here
    },

    editorInit: function () {
      require('brace/ext/language_tools')//language extension prerequsite...
      require('brace/mode/html')
      require('brace/mode/python')//language
      require('brace/mode/javascript')
      require('brace/mode/java')
      require('brace/mode/prolog')
      require('brace/mode/csharp')
      require('brace/mode/less')
      require('brace/theme/monokai')
      require('brace/snippets/python')//snippet
      require('brace/snippets/javascript')
      require('brace/snippets/java')
      require('brace/snippets/prolog')
      require('brace/snippets/csharp')
    }
  }

}

</script>

<style>

.editor {
  margin-top: 1.5em;
  border: solid lightgray 2px;
}

.submitBtn {
  margin-top: 1.5em;
}

.editorDiv {
  margin-top: 1.5em;
}

</style>
