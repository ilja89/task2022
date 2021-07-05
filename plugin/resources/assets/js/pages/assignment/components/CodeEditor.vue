<template>
  <div id="app">
    <AceEditor
        v-model="content"
        @init="editorInit"
        lang="python"
        theme="monk"
        width="100%"
        height="200px"
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
            exec: dataSumit,
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
  props: {
    form: {required: true}
  },

  components: {
    AceEditor
  },

  methods: {
    editorInit: function () {
      require('brace/ext/language_tools') //language extension prerequsite...
      require('brace/mode/html')
      require('brace/mode/python')    //language
      require('brace/mode/less')
      require('brace/theme/monokai')
      require('brace/snippets/python') //snippet
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
