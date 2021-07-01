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


      <MonacoEditor class="editor"
                    language="javascript"
                    theme="vs"
                    height="600"
                    :code="code"
                    :editorOptions="options"
                    @mounted="onMounted"
                    @codeChange="onCodeChange"
      >
      </MonacoEditor>

      <input type="hidden" id="code" name="code" v-model="form.fields.code">

    </div>

  </fieldset>

</template>

<script>

import MonacoEditor from 'vue-monaco-editor';
import { CharonTextInput } from '../../../components/form';
import { EmitEventOnInputChange } from "../../../mixins";

export default {

  name: "CodeEditorSection",

  mixins: [ EmitEventOnInputChange ],

  props: {
    form: {required: true}
  },

  components: {
    MonacoEditor,
    CharonTextInput
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