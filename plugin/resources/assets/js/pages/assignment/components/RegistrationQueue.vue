<template>
  <v-card class="mx-auto mb-4">
    <v-card-text class="grey lighten-4">
      <v-container class="spacing-playground pa-3" fluid>
        <v-card-title>
          {{ translate('queueText') }}
        </v-card-title>

        <v-layout column style="height: 125vh">
          <v-flex v-if="labEnded">
            <v-card-title>
              {{ translate('labEndedText') }}
            </v-card-title>
          </v-flex>
          <v-flex v-else md6 style="overflow: auto">
            <div v-if="labStarted">
              <v-card-title>
                {{ translate('labTeachersText') }}
              </v-card-title>
              <v-data-table
                :headers="labTeachersHeaders"
                :items="labTeachers"
                :hide-default-footer="true"
              >
              </v-data-table>
            </div>
            <div>
              <v-card-title>
                {{ translate('labQueueText') }}
              </v-card-title>
              <v-data-table
                :headers="getStudentsQueueHeaders"
                :items="studentsQueue"
                :item-class="currentUserRowBackground"
              >
              </v-data-table>
            </div>

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
        {text: this.translate("teacherText"), value: 'teacher_name', align: 'center'},
        {text: this.translate("charonText"), value: 'charon', align: 'center'},
        {text: this.translate("availabilityText"), value: 'availability', align: 'center'},
      ],
      labTeachers: [],
      studentsQueue: [],
      timer: '',
      labStarted: Date.parse(this.lab_start) <= Date.now(),
      labEnded: Date.parse(this.lab_end) <= Date.now()
    }
  },

  computed: {
    getStudentsQueueHeaders () {
      let queue = [
        {text: this.translate("nrInQueueText"), value: 'queue_pos', align: 'start', sortable: false},
        {text: this.translate("charonText"), value: 'charon_name', align: 'center', sortable: false},
        {text: this.translate("estimatedStartTimeText"), value: 'estimated_start', align: 'center', sortable: false},
        {text: this.translate("timeLeftText"), value: 'time_left', align: 'center', sortable: false}
      ];
      if (this.labStarted) {
          queue.push({text: this.translate("studentText"), value: 'student_name', sortable: false});
      }
      return queue;
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
    },

    currentUserRowBackground: function (item) {
      return item.student_name ? 'user-row' : ''
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

.user-row {
  background-color: rgba(180, 236, 200, 0.57)
}

</style>
