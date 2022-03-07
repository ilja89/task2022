<template>
  <v-card>
    <div class="field">
      <h3>Uniid - {{this.match.uniid}}</h3><br>
      <h3>Match percentage - {{this.match.percentage}}%</h3><br>

      <div class="columns is-gapless code-container" :class="{ 'is-round': isRound }">

        <div class="column  is-narrow">
          <div class="line-number-container">
                <span v-for="n in activeFile.numbers" class="line-number-position">
                    <span class="line-number">{{ n }}</span>
                </span>
          </div>
        </div>

        <pre class="code column code-column" v-highlightjs="activeFile.contents"><code :class="testerType"></code></pre>
      </div>
    </div>
    <div class="field">
      <h3>Uniid - {{this.match.other_uniid}}</h3><br>
      <h3>Match percentage - {{this.match.other_percentage}}%</h3><br>

      <div class="columns is-gapless code-container" :class="{ 'is-round': isRound }">

        <div class="column  is-narrow">
          <div class="line-number-container">
                <span v-for="n in activeOtherFile.numbers" class="line-number-position">
                    <span class="line-number">{{ n }}</span>
                </span>
          </div>
        </div>

        <pre class="code column code-column" v-highlightjs="activeOtherFile.contents"><code :class="testerType"></code></pre>
      </div>
    </div>
  </v-card>

</template>

<script>

export default {

  props: {
    match: {required: true},
    testerType: {required: true},
    isRound: {
      type: Boolean,
      default: true,
    },
  },
  data() {
    return {
      match: null
    }
  },

  computed: {
    activeFile() {
      let match = this.match;

      return {
        contents: match.code.trim().replace(/</g, '&lt;').replace(/>/g, '&gt;'),
        numbers: match.code.trim().split(/\r\n|\r|\n/).length,
      }
    },
    activeOtherFile() {
      let match = this.match

      return {
        contents: match.other_code.trim().replace(/</g, '&lt;').replace(/>/g, '&gt;'),
        numbers: match.other_code.trim().split(/\r\n|\r|\n/).length,
      }
    },
  },
}
</script>

<style lang="scss" scoped>

.field {
  width: 50%;
  float: left;
  padding: 5px;
}

$code-font-size: 14px;
$code-line-height: 23px;

.line-number {
  float: right;
  padding-left: 10px;
  padding-right: 10px;
  font-size: $code-font-size;
  line-height: $code-line-height;
  font-family: monospace;
}

.columns.code-container {

  .line-number-container {
    display: flex;
    flex-direction: column;
    height: 100%;
    padding-top: 1.25rem;
    padding-bottom: 1.25rem;

    background: darken(#fafafa, 5%);
    border: 1px solid #dbdbdb;
  }

  .file-tree-container {
    overflow: auto;
  }
}

pre.code {
  border: 1px solid #dbdbdb;
  border-left: none;
  overflow-x: scroll;
  background-color: #fafafa;
  height: 100%;
  padding: 0;

  code {
    padding: 1.25rem 1.25rem 1.25rem 0.5rem;
    min-height: 4rem;
    line-height: $code-line-height;
    font-size: $code-font-size;
    font-family: monospace;
  }
}

.code-container.is-round {

  .line-number-container {
    border-bottom-left-radius: 5px;
    border-top-left-radius: 5px;
  }

  .code {
    border-top-right-radius: 5px;
    border-bottom-right-radius: 5px;
  }
}

.code-column {
  position: relative;
  overflow-x: scroll;
}

@media (max-width: 768px) {
  .columns.code-container {

    .code code {
      padding-left: 1.25rem;
    }

    .line-number-container {
      display: none;
    }
  }
}

.review-comment {
  width: 100%;
  flex-wrap: wrap;
  padding: 10px;
  background-color: white;
}

.review-comment-button {
  background: darken(#d6d7d7, 5%);
}

.gray-part {
  background-color: darken(#fafafa, 5%);
}

.is-gapless.code-container {
  margin-bottom: 0;
}

</style>
