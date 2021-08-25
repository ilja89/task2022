<template>
  <v-card class="mx-auto mb-4">
    <v-card-text class="grey lighten-4">
      <v-container class="spacing-playground pa-3" fluid>

        <v-layout column style="height: 125vh">
          <v-flex md6 style="overflow: auto">
            <v-card-title>
              {{ translate('queueStatusText') }}
            </v-card-title>
            <v-data-table
                :headers="teachersLiveQueueHeaders"
                :items="teachersLiveQueueTestItems"
            >
            </v-data-table>
            <v-card-title>
              {{ translate('queuePositionText') }}
            </v-card-title>
            <v-data-table
                :headers="headers"
                :items="testItems"
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

  data() {
    return {
      testItems: [
        {
          queue_nr: 2,
          name: 'charon name',
          start_time: '01.09.2021 12:20',
          student: 'student name',
        },
      ],
      teachersLiveQueueTestItems: [
        {
          teacher: 'Teacher 1',
          name: 'Charon 1',
          duration: '00:10',
          start_time: '14:07',

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
    }
  },
}
</script>

<style>

</style>
