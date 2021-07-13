<template>
  <div class="editorDiv">

    <span>Language: {{language}}</span>

    <AceEditor
        class="editor"
        v-model="content"
        @input="dataSubmit"
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
    />

  </div>
</template>

<script>
import AceEditor from 'vuejs-ace-editor';

export default {
  name: "App",

  components: {
    AceEditor
  },

  props: {
    language: { required: true },
    code: { require: true },
    codes: { require: true },
    codeId: { required: true }
  },

  data() {
    return {
      content: this.code,
      lang: this.language
    }
  },

  beforeMount() {
    if (this.language === 'javang') {
      this.lang = 'java';
    }
  },

  computed: {
    dataSubmit() {
      this.codes[this.codeId].contents = this.content;
    },
  },

  methods: {
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

.editorDiv {
  margin-top: 1.5em;
}

</style>
