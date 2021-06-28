<template>

  <MonacoEditor class="editor"
                language="javascript"
                height="75%"
                theme="vs"
                :code="code"
                :editorOptions="options"
                @mounted="onMounted"
                @codeChange="onCodeChange"
  >
  </MonacoEditor>

</template>

<script>

import MonacoEditor from 'vue-monaco-editor';

export default {

  name: "code-editor",

  data () {
    return {
      code: 'function hello() {\n\talert(\'Hello world!\');\n}',
      options: {
        selectOnLineNumbers: true
      }
    }
  },

  components: {
    MonacoEditor
  },

}

</script>

<style>

.editor {
  margin-top: 1.5em;
  border: solid lightgray 2px;
}

</style>
