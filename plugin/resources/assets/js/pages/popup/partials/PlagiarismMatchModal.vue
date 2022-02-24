<template>
  <v-dialog v-model="isActive" width="90%" style="position: relative; z-index: 3000"
            transition="dialog-bottom-transition">
    <template v-slot:activator="{ on, attrs }">
      <v-btn icon
             :class="{ signal: 'green'}"
             @click="onClickMatchInformation"
             v-bind="attrs"
             v-on="on"
      >
        <v-icon aria-label="Match information" role="button" aria-hidden="false">mdi-eye</v-icon>
      </v-btn>
    </template>

    <v-card style="background-color: white; overflow-y: auto;">
      <v-toolbar dark>
        <span class="headline">({{this.match.percentage}}%) - ({{this.match.other_percentage}}%)</span>

        <v-spacer></v-spacer>

        <v-btn color="error" @click="isActive = false">
          Close
        </v-btn>
      </v-toolbar>

      <v-card-text class="pt-4">
        <a v-bind:href="this.match.moss_url" target="_blank">Moss match url</a><br><br>
        <h1 v-if="toggleShowSimilarities">Showing full files</h1>
        <h1 v-else>Showing only similar sections</h1>
        <br>
        <toggle-button @buttonClicked="showSimilarities($event)"></toggle-button>
        <div v-if="!toggleShowSimilarities">
          <match-files-component :match="this.match" :tester-type="testerType"></match-files-component>
        </div>
        <div v-if="toggleShowSimilarities">
          <match-similarities-component :similarities="this.match.similarities" :tester-type="testerType" :uniid="this.match.uniid" :other_uniid="this.match.other_uniid"></match-similarities-component>
        </div>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>

<script>
import {Translate} from '../../../mixins'
import {ToggleButton} from "../../../components/partials";
import MatchFilesComponent from "../../../components/partials/MatchFilesComponent";
import MatchSimilaritiesComponent from "../../../components/partials/MatchSimilaritiesComponent";

export default {
  name: "plagiarism-match-modal",

  mixins: [Translate],

  components: {
    MatchSimilaritiesComponent,
    MatchFilesComponent,
    ToggleButton
  },

  props: {
    match: {required: true}
  },

  data() {
    return {
      isActive: false,
      toggleShowSimilarities: false,
      testerType: "python"
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
    activeSimilarity() {
      let similarity = this.match.similarities.find(similarity => {
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
        other_lines: other_lines
      }
    },
  },

  mounted() {
  },

  methods: {
    onClickMatchInformation() {
      this.isActive = true;
    },
    showSimilarities(bool) {
      this.toggleShowSimilarities = bool;
    },
  },
}
</script>
