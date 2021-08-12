<template>
  <div class="editorDiv">

    <label for="content">Language: {{language}}</label>
    <textarea class="editor"
              id="content"
              v-model="content"
              :readonly="read_only"
              rows="28"
              @input="dataSubmit">
    </textarea>

  </div>
</template>

<script>

export default {
  name: "App",

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
  },

}

</script>

<style>

.editor {
  margin-top: 1.5em;
  border: solid lightgray 2px;
  width: 100%;
}

.editorDiv {
  margin-top: 1.5em;
}

</style>
