<template>
  <div class="editorDiv">

    <label for="content">{{ translate('programmingLanguage') }}: {{language}}</label>
    <textarea id="copyTextArea" class="textareaForCopy"></textarea>
    <textarea class="editor"
              id="content"
              v-model="content"
              :readonly="read_only"
              rows="28"
              @input="dataSubmit">
    </textarea>
    <v-btn class="ma-2 submitBtn" small tile outlined color="primary" @click="copyToClipBoard">
      {{ translate('copyButton') }}
    </v-btn>
  </div>
</template>

<script>

import Translate from "../../../mixins/Translate";

export default {
  mixins: [Translate],

  props: {
    language: { required: true },
    codes: { required: true },
    codeId: { required: true },
    allow_submission: {required: true}
  },

  data() {
    return {
      content: this.codes[this.codeId].contents,
      lang: this.language,
      read_only: this.allow_submission < 1
    }
  },

  methods: {

    dataSubmit() {
      this.codes[this.codeId].contents = this.content;
    },
    copyToClipBoard() {
      const id = "copyTextArea";
      let existsTextarea = document.getElementById(id);
      document.querySelector("body").appendChild(existsTextarea);
      existsTextarea.value = this.content;

      existsTextarea.select();
      try {
        const status = document.execCommand('copy');
        if(!status){
          VueEvent.$emit('show-notification', 'Cannot copy text.\n', 'danger');
        }else{
          VueEvent.$emit('show-notification', 'The text is now on the clipboard.\n');
        }
      } catch (err) {
        VueEvent.$emit('show-notification', 'Unable to copy.\n' + err, 'danger');
      }
    },
  },

}

</script>

<style>

.textareaForCopy {
  top: 0;
  left: 0;
  border: none;
  outline: none;
  box-shadow: none;
  background: transparent;
  padding: 0;
  position: fixed;
  width: 1px;
  height: 1px;
}

.editor {
  margin-top: 1.5em;
  border: solid lightgray 2px;
  width: 100%;
  resize:none;
}

.editorDiv {
  margin-top: 1.5em;
}

</style>
