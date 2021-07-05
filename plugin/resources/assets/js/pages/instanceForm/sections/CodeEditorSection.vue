<template>

  <fieldset class="clearfix collapsible" id="id_modstandardelshdr">

<!--    <legend class="ftoggler">{{ translate('grouping') }}</legend>-->
    <legend class="ftoggler">Code Editor</legend>

    <div class="fcontainer clearfix fitem">

      <label> Add code editor to this charon:
        <input type="checkbox" name="editor_set" v-model="form.fields.editor_set" value="true">
      </label>

      <charon-text-input v-if="form.fields.editor_set"
                         name="file_name"
                         label="File Name"
                         :required="true"
                         :value="form.fields.file_name"
                         @input-was-changed="onFileNameChanged"
      >
      </charon-text-input>


<!--      <MonacoEditor class="editor"-->
<!--                    language="javascript"-->
<!--                    theme="vs"-->
<!--                    height="600"-->
<!--                    :code="code"-->
<!--                    :editorOptions="options"-->
<!--                    @mounted="onMounted"-->
<!--                    @codeChange="onCodeChange"-->
<!--      >-->
<!--      </MonacoEditor>-->

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

// import MonacoEditor from 'vue-monaco-editor';
import { CharonTextInput } from '../../../components/form';
import { EmitEventOnInputChange } from "../../../mixins";
import AceEditor from 'vuejs-ace-editor';


export default {

  name: "CodeEditorSection",

  mixins: [ EmitEventOnInputChange ],

  props: {
    form: {required: true}
  },

  components: {
    // MonacoEditor,
    CharonTextInput,
    AceEditor
  },

  data () {
    return {
      code: '',
      options: {
        selectOnLineNumbers: true
      }
    }
  },

  methods: {
    editorInit: function () {
      require('brace/ext/language_tools') //language extension prerequsite...
      require('brace/mode/html')
      require('brace/mode/python')    //language
      require('brace/mode/less')
      require('brace/theme/monokai')
      require('brace/snippets/python') //snippet
    },

    onMounted(editor) {
      this.editor = editor;
    },

    onCodeChange() {
      if (this.form.fields.editor_set === true) {
        document.getElementById('code').value = this.editor.getValue();
      } else {
        document.getElementById('code').value = '';
        this.editor.value = '';
      }
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