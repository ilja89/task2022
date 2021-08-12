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

<!--    <AceEditor-->
<!--        class="ace_editor"-->
<!--        v-model="content"-->
<!--        @input="dataSubmit"-->
<!--        @init="editorInit"-->
<!--        :lang="lang"-->
<!--        theme="crimson_editor"-->
<!--        width="100%"-->
<!--        height="500px"-->
<!--        :options="{-->
<!--        enableBasicAutocompletion: true,-->
<!--        enableLiveAutocompletion: true,-->
<!--        fontSize: 14,-->
<!--        highlightActiveLine: true,-->
<!--        enableSnippets: true,-->
<!--        showLineNumbers: true,-->
<!--        tabSize: 2,-->
<!--        showPrintMargin: false,-->
<!--        showGutter: true,-->
<!--        readOnly: read_only,-->
<!--        }"-->
<!--    />-->

  </div>
</template>

<script>
import AceEditor from 'vuejs-ace-editor';

export default {
  name: "App",

  // components: {
  //   AceEditor
  // },

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

    // /**
    //  * Ace-code editor now supports only html, python, javascript, java, prolog and C#,
    //  * but more languages in these method like these: require('brace/mode/language'), where
    //  * language is programming language you need.
    //  * For example: require('brace/mode/python').
    //  */
    // editorInit: function () {
    //   require('brace/ext/language_tools') //language extension prerequsite...
    //   require('brace/mode/html') //language
    //   require('brace/mode/python')
    //   require('brace/mode/javascript')
    //   require('brace/mode/java')
    //   require('brace/mode/prolog')
    //   require('brace/mode/csharp')
    //   require('brace/mode/less')
    //   require('brace/theme/crimson_editor')
    //   require('brace/snippets/python') //snippet
    //   require('brace/snippets/javascript')
    //   require('brace/snippets/java')
    //   require('brace/snippets/prolog')
    //   require('brace/snippets/csharp')
    // }
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

.ace_editor {
  margin-top: 1.5em;
  border: solid lightgray 2px;
}

</style>
