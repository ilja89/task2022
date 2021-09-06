<template>
  <div class="editorDiv">

    <label for="content">{{ translate('programmingLanguage') }}: {{language}}</label>
    <textarea id="copyTextArea" class="textareaForCopy"></textarea>

    <AceEditor
        class="editor"
        v-model="content"
        id="content"
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
        highlightSelectedWord: true,
        enableSnippets: true,
        showLineNumbers: true,
        tabSize: 4,
        showPrintMargin: false,
        showGutter: true,
        readOnly: read_only,
        }"
    />

    <a class="button is-link" @click="copyToClipBoard">
      {{ translate('copyButton') }}
    </a>
  </div>
</template>

<script>
import AceEditor from 'vuejs-ace-editor';
import Translate from "../../../mixins/Translate";

export default {
  mixins: [Translate],

  components: {
    AceEditor
  },

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
  mounted() {
    VueEvent.$on('change-editor', (codes) => {
      this.content = codes[this.codeId].contents
    });
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
        if (!status) {
          VueEvent.$emit('show-notification', 'Cannot copy text.\n', 'danger');
        } else {
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
