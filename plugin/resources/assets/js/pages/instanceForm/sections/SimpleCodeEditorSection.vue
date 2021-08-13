<template>
  <fieldset class="clearfix collapsible" id="id_modstandardelshdr">
    <legend class="ftoggler">Code Editor</legend>
      <div class="fcontainer clearfix fitem">

        <label>
          <input id="setEditor" type="checkbox" name="allow_submission"
                 v-model="form.fields.allow_submission" value="true">
          Allow code submission on page
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
            <label for="content">Language: {{language}}</label>
            <textarea class="editor"
                      id="content"
                      v-model="form.fields.files[current_index].content"
                      rows="28">
            </textarea>
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
import {mdiDelete} from '@mdi/js'
import {Charon} from "../../../api";

export default {

  name: "AdvancedCodeEditorSection",

  props: {
    form: {required: true}
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

.editor {
  margin-top: 1.5em;
  border: solid lightgray 2px;
  width: 100%;
  resize: none;
}

</style>
