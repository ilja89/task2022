<template>

  <div class="fcontainer clearfix fitem">

    <label>
      <input id="setEditor" type="checkbox" name="editor_set" v-model="form.fields.editor_set" value="true">
      Add code editor to this charon
    </label>

    <h2>Source Files</h2>

    <v-btn class="ma-2 submitBtn" small tile outlined color="primary">
      + Create Source File
    </v-btn>

    <p>Language: python</p>

    <AceEditor
        class="editor"
        v-model="content"
        @init="editorInit"
        lang="python"
        theme="crimson_editor"
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

  name: "AdvancedCodeEditorSection",

  props: {
    form: {required: true}
  },

  components: {
    AceEditor
  },

  data() {
    return {
      content: '',
    }
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
      require('brace/theme/crimson_editor')
      require('brace/snippets/python')//snippet
      require('brace/snippets/javascript')
      require('brace/snippets/java')
      require('brace/snippets/prolog')
      require('brace/snippets/csharp')
    }
  },

}

</script>

<style scoped>

</style>