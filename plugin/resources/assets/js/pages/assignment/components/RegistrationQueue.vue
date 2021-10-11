<template>
  <v-card class="mx-auto mb-4">
    <v-card-text class="grey lighten-4">
      <v-container class="spacing-playground pa-3" fluid>
<!--        <v-card-title>-->
<!--          {{ translate('queueText') }}-->
<!--        </v-card-title>-->

        <v-layout column style="height: 125vh">
          <v-flex md6 style="overflow: auto">
            {{ translate('teachersQueueText') }}
            <v-data-table
              :headers="teachersLiveQueueHeaders"
              :items="teachersLiveQueueTestItems"
              :hide-default-footer="true"
              @update:items="updateDataLiveQueue"
            >
            </v-data-table>
            {{ translate('liveQueueText') }}
            <v-data-table
                :headers="headers"
                :items="items"
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

  props: ['items'],

  mixins: [Translate],

  data() {
    return {
      teachersLiveQueueTestItems: [
        {
          teacher: 'Teacher 1', charons: 'ex03', count: 1, availability: 'Defending'
        },
        {
          teacher: 'Teacher 2', charons: 'ex01, ex03', count: 2, availability: 'Defending'
        },
        {
          teacher: 'Teacher 3', charons: 'ex02', count: 1, availability: 'Defending'
        },
        {
          teacher: 'Teacher 4', charons: '', count: 0, availability: 'Free'
        },
      ],
      teachersLiveQueueHeaders: [
        {text: this.translate("teacherText"), value: 'teacher'},
        {text: this.translate("defendingCharonsText"), value: 'charons'},
        {text: this.translate("defendingStudentsCountText"), value: 'count'},
        {text: this.translate("availabilityText"), value: 'availability'},
      ],
      headers: [
        {text: this.translate("nrInQueueText"), value: 'queue_pos', align: 'start', sortable: false},
        {text: this.translate("charonText"), value: 'charon_name', sortable: false},
        {text: this.translate("estimatedStartTimeText"), value: 'approx_start_time', sortable: false},
        {text: this.translate("studentText"), value: 'student_name', sortable: false},
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
