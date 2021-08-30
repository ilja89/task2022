<template>
  <v-card class="mx-auto mb-4">
    <v-card-text class="grey lighten-4">
      <v-container class="spacing-playground pa-3" fluid>

        <v-layout column style="height: 125vh">
          <v-flex md6 style="overflow: auto">
            <v-card-title>
              {{ translate('queueStatusText') }}
              <v-spacer></v-spacer>
              <v-btn @click="dataUpdate">
                {{ translate('updateQueueText') }}
              </v-btn>
            </v-card-title>
            <v-data-table
                :headers="teachersLiveQueueHeaders"
                :items="teachersLiveQueueTestItems"
                :hide-default-footer="true"
                @update:items="updateDataLiveQueue"
            >
            </v-data-table>
            <v-card-title>
              {{ translate('placeInQueueText') }}
            </v-card-title>
            <v-data-table
                :headers="headers"
                :items="testItems"
                @update:items="updateDataPlaceInQueue"
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

export default {
  name: "registration-queue",

  mixins: [Translate],

  props: ['queueInterval'],

  data() {
    return {
      testItems: [
        {
          queue_nr: 2,
          name: 'charon name',
          estimated_start_time: '16.09.2021 12:20',
          student: 'student name',
        },
      ],
      teachersLiveQueueTestItems: [
        {
          teacher: 'Teacher 1', name: 'charon1', duration: '00:15', start_time: '15:00',
        },
        {
          teacher: 'Teacher 2', name: 'ch2', duration: '00:05', start_time: '15:05',
        },
        {
          teacher: 'Teacher 3', name: 'charon3', duration: '00:15', start_time: '15:00',
        },
      ],
      headers: [
        {text: this.translate("nrInQueueText"), value: 'queue_nr', align: 'start', sortable: false},
        {text: this.translate("charonText"), value: 'name', sortable: false},
        {text: this.translate("estimatedStartTimeText"), value: 'estimated_start_time', sortable: false},
        {text: this.translate("studentText"), value: 'student', sortable: false},
      ],
      teachersLiveQueueHeaders: [
        {text: this.translate("teacherText"), value: 'teacher'},
        {text: this.translate("charonText"), value: 'name', sortable: false},
        {text: this.translate("estimatedDurationText"), value: 'duration', sortable: false},
        {text: this.translate("startTimeText"), value: 'start_time', sortable: false},
      ],
      intervalOne: null,
      intervalTwo: null,
    }
  },
  watch: {
    queueInterval: function (queueInterval){
      if (queueInterval === true){
        this.intervalOne = setInterval(this.updateDataLiveQueue, 5000); //timeout for testing is 5 sec! in integration issue choose reasonable time!
        this.intervalTwo = setInterval(this.updateDataPlaceInQueue, 5000); //timeout for testing is 5 sec! in integration issue choose reasonable time!
      }
      if (queueInterval === false){
        clearInterval(this.intervalOne);
        clearInterval(this.intervalTwo);
      }
    }
  },
  methods: {
    dataUpdate(){
      this.updateDataLiveQueue();
      this.updateDataPlaceInQueue();
    },
    updateDataLiveQueue(){
      this.teachersLiveQueueTestItems = [
        {
          teacher: 'Teacher 1', name: 'charon1', duration: '00:15', start_time: '15:00',
        },
        {
          teacher: 'Teacher 2', name: 'ch4', duration: '00:25', start_time: '15:10',
        },
        {
          teacher: 'Teacher 3', name: '', duration: '', start_time: '',
        },
      ]
    },
    updateDataPlaceInQueue(){
      this.testItems = [
        {
          queue_nr: 1,
          name: 'charon name',
          estimated_start_time: '16.09.2021 12:20',
          student: 'student name',
        },
      ]
    },

  },
}
</script>

<style>

</style>
