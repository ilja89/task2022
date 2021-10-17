<template>
  <v-card class="mx-auto mb-4">
    <v-card-text class="grey lighten-4">
      <v-container class="spacing-playground pa-3" fluid>
        <v-card-title>
          {{ translate('queueText') }}
        </v-card-title>

        <v-layout column style="height: 125vh">
          <v-flex md6 style="overflow: auto">
            {{ translate('labTeachersText') }}
            <v-data-table
              :headers="defendingTeachersHeaders"
              :items="defendingTeachersTestItems"
              :hide-default-footer="true"
              @update:items="updateDefendingTeachers"
            >
            </v-data-table>
            {{ translate('studentsLiveQueueText') }}
            <v-data-table
                :headers="headers"
                :items="this.studentsQueue"
                @update:items="updateStudentsQueue"
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
      defendingTeachersTestItems: [
        {
          teacher: 'Teacher 1', charon: 'ex03', availability: 'Defending'
        },
        {
          teacher: 'Teacher 2', charon: 'ex01, ex03', availability: 'Defending'
        },
        {
          teacher: 'Teacher 3', charon: 'ex02', availability: 'Defending'
        },
        {
          teacher: 'Teacher 4', charon: '', availability: 'Free'
        },
      ],
      defendingTeachersHeaders: [
        {text: this.translate("teacherText"), value: 'teacher'},
        {text: this.translate("charonText"), value: 'charon'},
        {text: this.translate("availabilityText"), value: 'availability'},
      ],
      headers: [
        {text: this.translate("nrInQueueText"), value: 'queue_pos', align: 'start', sortable: false},
        {text: this.translate("charonText"), value: 'charon_name', sortable: false},
        {text: this.translate("estimatedStartTimeText"), value: 'approx_start_time', sortable: false},
        {text: this.translate("studentText"), value: 'student_name', sortable: false},
      ],
      timer: '',
      studentsQueue: this.items
    }
  },
  created () {
    this.timer = setInterval(this.dataUpdate, 15000);
  },
  methods: {
    dataUpdate(){
      this.updateDefendingTeachers();
      this.updateStudentsQueue();
    },
    updateDefendingTeachers(){
      this.defendingTeachersTestItems = [
        {
          teacher: 'Teacher 1', charon: 'ex02', availability: 'Defending'
        },
        {
          teacher: 'Teacher 2', charon: 'ex01', availability: 'Defending'
        },
        {
          teacher: 'Teacher 3', charon: '', availability: 'Free'
        },
        {
          teacher: 'Teacher 4', charon: '', availability: 'Free'
        },
      ]
    },
    updateStudentsQueue(){
      this.studentsQueue = []
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

</style>
