<template>
  <div>
    <p class="control tabs-right select-container files-select">
            <span class="select">

                <select name="file"
                        v-model="activeSimilarityId">
                    <option v-for="similarity in this.similarities"
                            :value="similarity.id">
                        {{ similarity.id }}
                    </option>
                </select>

            </span>
    </p>
    <v-card>
      <div class="field">

        <h3>Uniid - {{this.uniid}}</h3><br>
        <h3>Lines - {{activeSimilarity.lines_string}}</h3><br>

        <div class="columns is-gapless code-container" :class="{ 'is-round': isRound }">
          <div class="column  is-narrow">
            <div class="line-number-container">
                <span v-for="n in activeSimilarity.lines" class="line-number-position">
                    <span class="line-number">{{ n }}</span>
                </span>
            </div>
          </div>

          <pre class="code column code-column" v-highlightjs="activeSimilarity.code_block"><code :class="testerType"></code></pre>
        </div>
      </div>
      <div class="field">

        <h3>Uniid - {{this.other_uniid}}</h3><br>
        <h3>Lines - {{activeSimilarity.other_lines_string}}</h3><br>

        <div class="columns is-gapless code-container" :class="{ 'is-round': isRound }">
          <div class="column  is-narrow">
            <div class="line-number-container">
                <span v-for="n in activeSimilarity.other_lines" class="line-number-position">
                    <span class="line-number">{{ n }}</span>
                </span>
            </div>
          </div>

          <pre class="code column code-column" v-highlightjs="activeSimilarity.other_code_block"><code :class="testerType"></code></pre>
        </div>
      </div>
    </v-card>
  </div>
</template>

<script>

export default {

  props: {
    similarities: {required: true},
    testerType: {required: true},
    uniid: {required: true},
    other_uniid: {required: true},
    isRound: {
      type: Boolean,
      default: true,
    },
  },
  data() {
    return {
      activeSimilarityId: this.similarities[0].id
    }
  },

  computed: {
    activeSimilarity() {
      let similarity = this.similarities.find(similarity => {
        return similarity.id === this.activeSimilarityId;
      });
      let lines_count = similarity.lines.split('-');
      let other_lines_count = similarity.other_lines.split('-');
      let lines = [];
      let other_lines = [];
      for (let i = parseInt(lines_count[0] - 1); i <= parseInt(lines_count[1]); i++) {
        lines.push(i)
      }
      for (let i = parseInt(other_lines_count[0] - 1); i <= parseInt(other_lines_count[1]); i++) {
        other_lines.push(i)
      }

      return {
        id: similarity.id,
        code_block: similarity.code_block,
        other_code_block: similarity.other_code_block,
        lines: lines,
        lines_string: similarity.lines,
        other_lines: other_lines,
        other_lines_string: similarity.other_lines
      }
    },
  },

  mounted() {
  },

  methods: {
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
