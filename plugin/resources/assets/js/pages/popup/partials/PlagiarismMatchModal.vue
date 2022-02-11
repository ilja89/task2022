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
        <span class="headline">Modal information here</span>

        <v-spacer></v-spacer>

        <v-btn color="error" @click="isActive = false">
          Close
        </v-btn>
      </v-toolbar>

      <v-card-text class="pt-4">
        <div>
          {{ this.match }}
        </div>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>

<script>
import {Translate} from '../../../mixins'

export default {
  name: "plagiarism-match-modal",

  mixins: [Translate],

  components: {
  },

  props: {
    match: {required: true}
  },

  data() {
    return {
      isActive: false,
    }
  },

  computed: {
  },

  mounted() {
  },

  methods: {
    onClickMatchInformation() {
      this.isActive = true;
    },
  },
}
</script>
