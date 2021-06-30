<template>

  <fieldset class="clearfix collapsible" id="id_modstandardelshdr">

<!--    <legend class="ftoggler">{{ translate('grouping') }}</legend>-->
    <legend class="ftoggler">Code Editor</legend>

    <div class="fcontainer clearfix fitem">

      <label> Add code editor to this charon:
        <input type="checkbox" v-model="form.fields.editor_set" value="1">
      </label>

      <charon-text-input v-if="form.fields.editor_set"
                         name="file_name"
                         label="File Name"
                         :required="true"
                         :value="form.fields.file_name"
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


    </div>

  </fieldset>

</template>

<script>

import MonacoEditor from 'vue-monaco-editor';
import { CharonTextInput } from '../../../components/form';

export default {

  name: "CodeEditorSection",

  props: {
    form: {required: true}
  },

  components: {
    MonacoEditor,
    CharonTextInput
  },

  data () {
    return {
      code: this.form.fields.code,
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
        this.form.fields.code = this.editor.getValue();
      }
      console.log(this.form.fields.code);
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