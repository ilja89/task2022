<template>
  <v-bottom-sheet v-model="sheet" inset style="position: relative; z-index: 1000">
    <template v-slot:activator="{ on, attrs }">
      <v-btn v-bind="attrs" v-on="on" icon @click="sheet = true">
          <img alt="queue" height="24px" src="pix/line.png" width="24px">
      </v-btn>
    </template>
    <div>
      <v-toolbar color="success" dark>
        <span class="headline">{{ translate('queueStatusText') }}</span>

        <v-spacer></v-spacer>

        <v-btn color="error" @click="sheet = false">
          {{ translate('closeText') }}
        </v-btn>
      </v-toolbar>

      <v-sheet height="80vh" class="pt-4 px-4">
        <registration-queue></registration-queue>
      </v-sheet>
    </div>
  </v-bottom-sheet>
</template>



<script>
import {Translate} from "../../../mixins";
import RegistrationQueue from "./RegistrationQueue";

export default {
  name: "registration-queue-sheet",

  mixins: [Translate],

  components:{
    RegistrationQueue
  },

  props: ['labData'],

  data() {
    return {
      sheet: false,
    };
  },

}

</script>

<style scoped>

</style>
