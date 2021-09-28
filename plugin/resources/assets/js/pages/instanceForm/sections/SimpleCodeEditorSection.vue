<template>
    <fieldset class="clearfix collapsible" id="id_modstandardelshdr_SCES">
      <legend class="ftoggler">{{ translate('codeEditorSection') }}</legend>
      <div class="fcontainer clearfix fitem">

        <label>
          <input id="setEditor" type="checkbox" name="allow_submission"
                 v-model="form.fields.allow_submission" value="true">
          {{ translate('allowCodeSubmission') }}
        </label>

        <p>{{ translate('sourceFiles') }}</p>

        <v-btn class="ma-2 submitBtn" small tile outlined color="primary" @click="addFile">
          {{ translate('createSourceFileButton') }}
        </v-btn>

        <ul v-for="(file, index) in form.fields.files">
          <li>
            <div class="fitem_ftext">
              <div class="fitemtitle">
                <label for="file_path">{{ translate('path') }}</label>
              </div>
              <p class="input-helper">{{ translate('pathToFileHelper')}}</p>
              <div class="felement ftext path">
                <v-tooltip
                    v-model="file.duplicate"
                    top
                >
                  <template v-slot:activator="{ attrs }">
                    <input
                        v-on:change="validationCheck()"
                        id="file_path"
                        v-bind:class="{'form-control': true, 'duplicate': file.duplicate, attrs}"
                        type="text"
                        :required="true"
                        v-model="file.path">
                  </template>
                  <span>{{ translate('pathWarning') }}</span>
                </v-tooltip>

                <v-btn
                    class="my-2 del_btn"
                    depressed
                    dark
                    @click="deleteFile(index)">
                  {{ translate('deleteButton') }}
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
            <label>{{ translate('sourceFile') }}
              <select class="custom-select select" v-model="current_index">
                <option v-if="file.path !== ''" v-bind:value="index" v-for="(file, index) in form.fields.files">{{ file.path }}</option>
                <option v-if="form.fields.files.length < 2 && form.fields.files[0].path === ''" disabled>{{ translate('insertFilePath') }}</option>
              </select>
            </label>
          </div>

          <div v-if="current_index < form.fields.files.length && form.fields.files[current_index].path !== ''">
            <label for="content">{{ translate('programmingLanguage') }}: {{language}}</label>

            <AceEditor
                class="editor"
                id="content"
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
                highlightSelectedWord: true,
                enableSnippets: true,
                showLineNumbers: true,
                tabSize: 4,
                showPrintMargin: false,
                showGutter: true,
              }"
            />
          </div>

          <div v-for="file in form.fields.files">
            <input type="hidden" :name="'files[' + file.id + '][path]'" :value="file.path">
            <input type="hidden" :name="'files[' + file.id + '][templateContents]'" :value="file.content">
          </div>
        </div>
      </div>
    </fieldset>
</template>
<script>
import AceEditor from 'vuejs-ace-editor';
import {mdiDelete} from '@mdi/js'
import {Charon} from "../../../api";
import Translate from "../../../mixins/Translate";

export default {
  mixins: [Translate],

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
      this.form.fields.files.forEach(file => {
        file.duplicate = false;
      });
      this.form.fields.files.forEach(file => {
        const v = file.path;
        if (v !== "" && values.includes(v)) {
          this.form.fields.files.forEach(file => {
            if (file.path === v) {
              file.duplicate = true;
            }
          });
        }
        values.push(v)
      });
    },

    defineLanguage(language_code) {
      Charon.getTesterLanguage(language_code, this.form.fields.course).then(response =>{
        this.language = response;
      })
    },

    addFile() {
      this.form.fields.files.push({"id": this.form.fields.files.length, "path": '', "content": '', "duplicate": false});
    },

    deleteFile(index) {
      this.form.fields.files.splice(index, 1);
      this.current_index = index - 1;
      if (this.current_index < 0) {
        this.current_index = 0;
      }
      this.validationCheck();
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

.editor {
  margin-top: 1.5em;
  border: solid lightgray 2px;
  width: 100%;
  resize: none;
}

</style>
