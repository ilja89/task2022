<template>
  <v-bottom-sheet v-model="sheet" inset style="position: relative; z-index: 1000">
    <template v-slot:activator="{ on, attrs }">
      <v-btn v-bind="attrs" v-on="on" icon @click="sheet = true">
        <img v-if="hasLoaded" alt="queue" height="24px" src="pix/line.png" width="24px">
        <img v-else alt="queue loading" height="24px" src="pix/refreshBlack.png"
             v-bind:class="'rotating'"
             width="24px">
      </v-btn>
    </template>
    <div v-if="hasLoaded">
      <v-toolbar color="success" dark>
        <span class="headline">{{ translate('queueStatusText') }}</span>

        <v-spacer></v-spacer>

        <v-btn color="error" @click="sheet = false">
          {{ translate('closeText') }}
        </v-btn>
      </v-toolbar>

      <v-sheet height="80vh" class="pt-4 px-4">
        <registration-queue :lab_start="this.labData.lab_start" :lab_end="this.labData.lab_end" :items="this.queueStatus" :defenseLabId="this.labData.defense_lab_id"></registration-queue>
      </v-sheet>
    </div>
  </v-bottom-sheet>
</template>



<script>
import {Translate} from "../../../mixins";
import RegistrationQueue from "./RegistrationQueue";
import {Lab} from "../../../api";

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
      queueStatus: null
    };
  },

  computed: {
    hasLoaded(){
      return !!this.queueStatus;
    }
  },

  methods: {
    getQueueStatus: function (){
      Lab.getLabQueueStatus(this.$store.state.charon.id, this.labData.defense_lab_id, this.$store.state.student_id,  (queueStatus)=>{
        this.queueStatus = queueStatus;
      });
    }
  },

  beforeMount(){
    this.getQueueStatus()
  },

}

</script>

<style scoped>

@import '../../../../../../public/css/buttons/refreshButton.css';

</style>
