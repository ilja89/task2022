<template>
  <v-card class="mx-auto mb-4">
    <v-card-text class="grey lighten-4">
      <v-container class="spacing-playground pa-3" fluid>
        <v-card-title>
          {{ translate('queueText') }}
        </v-card-title>

        <v-layout column style="height: 125vh">
          <v-flex v-if="checkLabEnded">
            <v-card-title>
              {{ translate('labEndedText') }}
            </v-card-title>
          </v-flex>
          <v-flex v-else md6 style="overflow: auto">
            <div v-if="checkLabStarted">
              <v-card-title>
                {{ translate('labTeachersText') }}
              </v-card-title>
              <v-data-table
                :headers="labTeachersHeaders"
                :items="labTeachers"
                :hide-default-footer="true"
                @update:items="dataUpdate"
              >
              </v-data-table>
            </div>

            <v-card-title>
              {{ translate('labQueueText') }}
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
    lab_start: {required: true},
    lab_end: {required: true},
    items: {required: true},
    defenseLabId: {required: true}
  },

  mixins: [Translate],

  data() {
    return {
      labTeachersHeaders: [
        {text: this.translate("teacherText"), value: 'teacher_name'},
        {text: this.translate("charonText"), value: 'charon'},
        {text: this.translate("availabilityText"), value: 'availability'},
      ],
      studentsQueueHeaders: [
        {text: this.translate("nrInQueueText"), value: 'queue_pos', align: 'start', sortable: false},
        {text: this.translate("charonText"), value: 'charon_name', sortable: false},
        {text: this.translate("estimatedStartTimeText"), value: 'estimated_start', sortable: false},
        {text: this.translate("studentText"), value: 'student_name', sortable: false},
      ],
      labTeachers: [],
      studentsQueue: [],
      timer: '',
      labStarted: Date.parse(this.lab_start) <= Date.now(),
      labEnded: Date.parse(this.lab_end) <= Date.now()
    }
  },

  computed: {
    checkLabStarted() {
      return this.labStarted;
    },

    checkLabEnded() {
      return this.labEnded;
    }
  },

  created () {
    this.timer = setInterval(this.dataUpdate, 15000);
    this.studentsQueue = this.items.registrations;
    this.labTeachers = this.items.teachers;
  },

  methods: {
    dataUpdate(){
      Lab.getLabQueueStatus(this.$store.state.charon.id, this.defenseLabId, this.$store.state.student_id,  (items)=>{
        this.studentsQueue = items.registrations;
        this.labTeachers = items.teachers;
        this.labStarted = Date.parse(items.lab_start) <= Date.now();
        this.labEnded = Date.parse(items.lab_end) <= Date.now();
        if (this.labEnded) {
          this.cancelAutoUpdate()
        }
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
  min-height: 1vh;
}

</style>
