<template>
  <v-bottom-sheet v-model="sheet" inset style="position: relative; z-index: 1000">
    <template v-slot:activator="{ on, attrs }">
      <v-btn class="mt-4 ml-4" v-bind="attrs" v-on="on" color="primary" outlined dense text @click="sheet = true">
        {{ translate('queueStatusText') }}
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
        <div>
          {{labData}}
        </div>
        <registration-queue></registration-queue>

        <v-row class="my-4">
          <v-btn class="mt-4 ml-4" outlined text color="error" @click="sheet = false">
            {{ translate('closeText') }}
          </v-btn>
        </v-row>
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
      item: [],
    };
  },

}

</script>

<style scoped>

</style>