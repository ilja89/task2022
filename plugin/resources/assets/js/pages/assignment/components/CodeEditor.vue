<template>
  <div class="editorDiv">

    <span>Language: {{language}}</span>

    <AceEditor
        class="editor"
        v-model="content"
        @input="dataSubmit"
        @init="editorInit"
        :lang="lang"
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
    <v-btn class="ma-2 submitBtn" small tile outlined color="primary" @click="copyToClipBoard">
      Copy
    </v-btn>
  </div>
</template>

<script>
import AceEditor from 'vuejs-ace-editor';

export default {
  name: "App",

  components: {
    AceEditor
  },

  props: {
    language: { required: true },
    codes: { require: true },
    codeId: { required: true }
  },

  data() {
    return {
      content: this.codes[this.codeId].contents,
      lang: this.language
    }
  },

  computed: {
    dataSubmit() {
      this.codes[this.codeId].contents = this.content;
    },
  },

  methods: {
    copyToClipBoard() {
      const id = "mycustom-clipboard-textarea-hidden-id";
      let existsTextarea = document.getElementById(id);

      if(!existsTextarea){
        const textarea = document.createElement("textarea");
        textarea.id = id;
        // Place in top-left corner of screen regardless of scroll position.
        textarea.style.position = 'fixed';
        textarea.style.top = 0;
        textarea.style.left = 0;
        // Ensure it has a small width and height. Setting to 1px / 1em
        // doesn't work as this gives a negative w/h on some browsers.
        textarea.style.width = '1px';
        textarea.style.height = '1px';
        // We don't need padding, reducing the size if it does flash render.
        textarea.style.padding = 0;
        // Clean up any borders.
        textarea.style.border = 'none';
        textarea.style.outline = 'none';
        textarea.style.boxShadow = 'none';
        // Avoid flash of white box if rendered for any reason.
        textarea.style.background = 'transparent';
        document.querySelector("body").appendChild(textarea);
        existsTextarea = document.getElementById(id);
      }
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
  }
}

</script>

<style>

.editor {
  margin-top: 1.5em;
  border: solid lightgray 2px;
}

.editorDiv {
  margin-top: 1.5em;
}

</style>
