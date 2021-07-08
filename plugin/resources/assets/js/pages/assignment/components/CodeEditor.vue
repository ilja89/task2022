<template>
  <div id="app" v-if="editor_set">

    <span>Language: {{language}}</span>

    <AceEditor
        class="editor"
        v-model="content"
        @init="editorInit"
        :lang="language"
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
    editor_set: { required: true },
    code: { require: true }
  },

  data() {
    return {
      content: '',
    }
  },

  methods: {
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

</style>
