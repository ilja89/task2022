<template>
  <v-card class="mx-auto mb-4">
    <v-card-text class="grey lighten-4">
      <v-container class="spacing-playground pa-3" fluid>
        <v-card-title>
          {{ translate('queueText') }}
        </v-card-title>

        <v-layout column style="height: 125vh">
          <v-flex md6 style="overflow: auto">

            <v-card-title>
              {{ translate('labTeachersText') }}
            </v-card-title>
            <v-data-table
              :headers="defendingTeachersHeaders"
              :items="defendingTeachers"
              :hide-default-footer="true"
              @update:items="dataUpdate"
            >
            </v-data-table>

            <v-card-title>
              {{ translate('studentsLiveQueueText') }}
            </v-card-title>
            <v-data-table
              :headers="studentsQueueHeaders"
              :items="studentsQueue"
              @update:items="dataUpdate"
            >

            </v-data-table>
          </v-flex>
        </v-layout>
      </v-container>
    </v-card-text>
  </v-card>
</template>

<script>
import {Translate} from "../../../mixins";
import {Lab} from "../../../api";

export default {
  name: "registration-queue",

  props: {
    items: {required: true},
    defenseLabId: {required: true}
  },

  mixins: [Translate],

  data() {
    return {
      defendingTeachers: [],
      defendingTeachersHeaders: [
        {text: this.translate("teacherText"), value: 'teacher'},
        {text: this.translate("charonText"), value: 'charon'},
        {text: this.translate("availabilityText"), value: 'availability'},
      ],
      studentsQueueHeaders: [
        {text: this.translate("nrInQueueText"), value: 'queue_pos', align: 'start', sortable: false},
        {text: this.translate("charonText"), value: 'charon_name', sortable: false},
        {text: this.translate("estimatedStartTimeText"), value: 'approx_start_time', sortable: false},
        {text: this.translate("studentText"), value: 'student_name', sortable: false},
      ],
      timer: '',
      studentsQueue: []
    }
  },
  created () {
    this.timer = setInterval(this.dataUpdate, 15000);
    this.studentsQueue = this.items.registrations;
    this.defendingTeachers = this.items.teachers
  },
  methods: {
    dataUpdate(){
      Lab.getLabQueueStatus(this.$store.state.charon.id, this.defenseLabId, this.$store.state.student_id,  (items)=>{
        this.studentsQueue = items.registrations;
        this.defendingTeachers = items.teachers;
      });
    },

    cancelAutoUpdate () {
      clearInterval(this.timer);
    }
  },

  beforeDestroy () {
    this.cancelAutoUpdate();
  }
}
</script>

<style>

.v-application--wrap {
  min-height: 1vh !important;
}

</style>
