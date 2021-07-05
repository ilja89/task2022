<template>

  <fieldset class="clearfix collapsible" id="id_modstandardelshdr">

<!--    <legend class="ftoggler">{{ translate('grouping') }}</legend>-->
    <legend class="ftoggler">Code Editor</legend>

    <div class="fcontainer clearfix fitem">

      <label> Add code editor to this charon:
        <input type="checkbox" name="editor_set" v-model="form.fields.editor_set" value="true">
      </label>

<!--      <input type="hidden" id="code" name="code" v-model="form.fields.code">-->

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

  </fieldset>

</template>

<script>

import AceEditor from 'vuejs-ace-editor';

export default {

  name: "CodeEditorSection",

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