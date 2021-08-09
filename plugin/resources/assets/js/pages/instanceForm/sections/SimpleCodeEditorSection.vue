<template>
  <fieldset class="clearfix collapsible" id="id_modstandardelshdr">
    <legend class="ftoggler">Code Editor</legend>
      <div class="fcontainer clearfix fitem">

        <label>
          <input id="setEditor" type="checkbox" name="editor_set" v-model="form.fields.editor_set" value="true">
          Add code editor to this charon
        </label>

        <p>Source Files</p>

        <v-btn class="ma-2 submitBtn" small tile outlined color="primary" @click="addFile">
          + Create Source File
        </v-btn>

        <ul v-for="(file, index) in form.fields.files">
          <li>
            <div class="fitem_ftext">
              <div class="fitemtitle">
                <label for="file_path">Path</label>
              </div>
              <p class="input-helper">Path to file.</p>
              <div class="felement ftext path">
                <input
                    v-on:change="validationCheck()"
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

        <div v-if="form.fields.files.length > 0">

          <div class="felement">
            <label> Source File:
              <select class="custom-select select" v-model="current_index">
                <option v-if="file.path !== ''" v-bind:value="index" v-for="(file, index) in form.fields.files">{{ file.path }}</option>
                <option v-if="form.fields.files.length < 2 && form.fields.files[0].path === ''" disabled>Insert file path to see it here and edit file content.</option>
              </select>
            </label>
          </div>

          <div v-if="current_index < form.fields.files.length && form.fields.files[current_index].path !== ''">
            <p>Language: {{language}}</p>

            <AceEditor
                class="editor"
                v-model="form.fields.files[current_index].content"
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
          <div v-for="file in form.fields.files">
            <input type="hidden" :name="'files[' + file.id + '][path]'" :value="file.path">
            <input type="hidden" :name="'files[' + file.id + '][contents]'" :value="file.content">
          </div>
        </div>
      </div>
  </fieldset>
</template>

<script>
import AceEditor from 'vuejs-ace-editor';
import {mdiDelete} from '@mdi/js'
import {Charon} from "../../../api";

export default {

  name: "AdvancedCodeEditorSection",

  props: {
    form: {required: true}
  },

  components: {
    AceEditor,
  },

  computed: {
    isEditing() {
      return window.isEditing;
    },
  },


  data() {
    return {
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
    validationCheck() {
      const values = [];
      $(".duplicate").removeClass("duplicate");
      const $inputs = $('input[class="form-control"]');
      $inputs.each(function() {
        const v = this.value;
        if (values.includes(v)) $inputs.filter(function() { return this.value === v }).addClass("duplicate");
        values.push(v);
      })
    },

    defineLanguage(language_code) {
      Charon.getTesterLanguage(language_code, this.form.fields.course).then(response =>{
        this.language = response;
      })
    },

    addFile() {
      this.form.fields.files.push({"id": this.form.fields.files.length, "path": '', "content": ''});
    },

    deleteFile(index) {
      this.form.fields.files.splice(index, 1);
      this.current_index = index - 1;
      if (this.current_index < 0) {
        this.current_index = 0;
      }
    },

    /**
     * Ace-code editor now supports only html, python, javascript, java, prolog and C#,
     * but more languages in these method like these: require('brace/mode/language'), where
     * language is programming language you need.
     * For example: require('brace/mode/python').
     */
    editorInit: function () {
      require('brace/ext/language_tools') //language extension prerequsite...
      require('brace/mode/html') //language
      require('brace/mode/python')
      require('brace/mode/javascript')
      require('brace/mode/java')
      require('brace/mode/prolog')
      require('brace/mode/csharp')
      require('brace/mode/less')
      require('brace/theme/crimson_editor')
      require('brace/snippets/python') //snippet
      require('brace/snippets/javascript')
      require('brace/snippets/java')
      require('brace/snippets/prolog')
      require('brace/snippets/csharp')
    }
  },

}

</script>

<style scoped>

.duplicate {
  box-shadow: inset 0 0 0 3px red;
}

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
