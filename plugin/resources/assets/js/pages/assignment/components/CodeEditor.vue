<template>

  <MonacoEditor class="editor"
                language="javascript"
                height="600"
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
      code: '',
      options: {
        selectOnLineNumbers: true
      }
    }
  },

  components: {
    MonacoEditor
  },

  methods: {

    onMounted(editor) {
      this.editor = editor;
    },

    onCodeChange() {
      console.log(this.editor.getValue());
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
