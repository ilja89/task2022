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

    <ul v-for="(file, index) in files">
      <li>
        <div class="fitem_ftext">
          <div class="fitemtitle">
            <label for="file_path">Path</label>
          </div>
          <p class="input-helper">Path to file.</p>
          <div class="felement ftext path">
            <input
                id="file_path"
                class="form-control"
                type="text"
                :required="true"
                v-model="file.path">
            <v-btn
                class="my-2 del_btn"
                depressed
                dark
                @click="deleteFile(index)">
              Delete
              <v-icon right>
                {{ mdiDelete }}
              </v-icon>
            </v-btn>
          </div>
        </div>
      </li>
    </ul>

    <div v-if="files.length > 0">

      <div class="felement">
        <label> Source File:
          <select class="custom-select select" v-model="current_index">
            <option v-if="file.path !== ''" v-bind:value="index" v-for="(file, index) in files">{{ file.path }}</option>
            <option v-if="files.length < 2 && files[0].path === ''" disabled>Insert file path to see it here and edit file content.</option>
          </select>
        </label>
      </div>

      <div v-if="current_index < files.length && files[current_index].path !== ''">
        <p>Language: {{language}}</p>

        <AceEditor
            class="editor"
            v-model="files[current_index].content"
            @init="editorInit"
            :lang="language"
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

  </div>

</template>

<script>
import AceEditor from 'vuejs-ace-editor';
import { mdiDelete } from '@mdi/js'

export default {

  name: "AdvancedCodeEditorSection",

  props: {
    form: {required: true}
  },

  components: {
    AceEditor,
  },

  data() {
    return {
      files: [],
      mdiDelete,
      current_index: 0,
      language: 'python',
    }
  },

  beforeMount() {
    let language_code = 1

    if (this.form.fields.tester_type === undefined) {
      language_code = this.form.fields.tester_type_code;
    } else {
      language_code = this.form.fields.tester_type;
    }
    this.defineLanguage(language_code);
  },

  mounted() {
    VueEvent.$on('tester-type-was-changed', (tester_type) => this.defineLanguage(tester_type));
  },

  methods: {

    defineLanguage(language_code) {
      let BreakException = {};

      try {
        this.form.tester_types.forEach(type => {
          if (type.code === language_code) {
            if (type.name === 'javang') {
              this.language = 'java';
            } else {
              this.language = type.name;
            }
            throw BreakException;
          }
        });
      } catch (e) {
        if (e !== BreakException) throw e;
      }
    },

    addFile() {
      this.files.push({"path": '', "content": ''});
      console.log(this.files);
      console.log(this.form.tester_types);
      console.log(this.form.fields.tester_type_code);
      console.log(this.form.fields.tester_type);
    },

    deleteFile(index) {
      this.files.splice(index, 1);
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
  justify-content: space-between;
  align-items: center;
}

ul {
  margin-top: 2em;
  margin-bottom: 2em;
  list-style-type: none
}

.del_btn {
  margin-left: 0.5em;
}

.select {
  width: 35em;
}

</style>