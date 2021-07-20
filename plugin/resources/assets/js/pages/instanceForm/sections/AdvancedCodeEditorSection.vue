<template>

  <div class="fcontainer clearfix fitem">

    <label>
      <input id="setEditor" type="checkbox" name="editor_set" v-model="form.fields.editor_set" value="true">
      Add code editor to this charon
    </label>

    <p>Source Files</p>

    <v-btn class="ma-2 submitBtn" small tile outlined color="primary" @click="addFile">
      + Create Source File
    </v-btn>

    <v-list-item v-for="(file) in files">
      <v-list-item-content>
        <div class="fcontainer clearfix">
          <div class="fitem fitem_ftext">
            <div class="fitemtitle">
              <label for="file_path">Path</label>
            </div>
            <p class="input-helper">Path to file.</p>
            <div class="felement ftext path">
              <v-btn
                  class="red text--white ma-2"
                  depressed
                  dark
              >
                <v-icon left>
                  {{ mdiDelete }}
                </v-icon>
                Delete
              </v-btn>
              <input
                  id="file_path"
                  class="form-control"
                  type="text" required="required"
                  v-model="file.path">
            </div>
          </div>
        </div>
      </v-list-item-content>
    </v-list-item>

    <div v-if="files.length > 0">
      <p>Language: python !!!!</p>

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

  </div>

</template>

<script>
import AceEditor from 'vuejs-ace-editor';
import { CharonTextInput } from '../../../components/form';
import { mdiDelete } from '@mdi/js'

export default {

  name: "AdvancedCodeEditorSection",

  props: {
    form: {required: true}
  },

  components: {
    AceEditor,
    CharonTextInput
  },

  data() {
    return {
      content: '',
      files: [],
      mdiDelete,
    }
  },

  methods: {

    addFile() {
      this.files.push({"path": '', "content": ''});
      console.log(this.files);
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

.path {
  display: flex;
  flex-direction: row;
}

</style>