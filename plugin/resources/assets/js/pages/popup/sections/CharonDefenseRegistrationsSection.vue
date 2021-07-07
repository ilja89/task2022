<template>
  <div>

    <popup-section
        title="Code showing registrations"
        subtitle="Here are all the registrations for code showing. Select your name in the 'Teacher name' and press start session - then progress will be automatically updated"
    >

      <v-card class="mx-auto" outlined light raised>
        <v-container class="spacing-playground pa-3" fluid>
          <v-row>

            <v-col cols="12" xs="12" sm="12" md="6" lg="3">
              <div class="helper">
                After
              </div>
              <div class="datepick">
                <datepicker :datetime="after"></datepicker>
                <input type="hidden" :value="after">
              </div>
            </v-col>

            <v-col cols="12" xs="12" sm="12" md="6" lg="3">
              <div class="helper">
                Before
              </div>
              <div class="datepick">
                <datepicker :datetime="before"></datepicker>
                <input type="hidden" :value="before">
              </div>
            </v-col>

            <v-col cols="12" xs="12" sm="4" md="4" lg="2">
              <div class="helper">
                Teacher name
              </div>

              <v-select
                  :disabled="isSessionActive"
                  class="mx-auto"
                  dense
                  single-line
                  item-text="fullname"
                  item-value="id"
                  :items="teachers"
                  v-model="filter_teacher"
              ></v-select>
            </v-col>

            <v-col cols="12" xs="12" sm="4" md="4" lg="2">
              <div class="helper">
                Progress
              </div>

              <v-select
                  class="mx-auto"
                  dense
                  :items="all_progress_types"
                  v-model="filter_progress"
              ></v-select>
            </v-col>

            <v-col cols="12" xs="12" sm="4" md="4" lg="2">
              <v-btn class="ma-2" tile outlined color="primary" dense @click="apply">
                Apply
              </v-btn>

              <v-btn class="ma-2" tile outlined color="error" dense @click="endSession"
                     v-if="isSessionActive">
                End session
              </v-btn>

              <v-btn class="ma-2" tile outlined color="primary" dense @click="startSession" v-else>
                Start session
              </v-btn>
            </v-col>

          </v-row>
        </v-container>
      </v-card>
    <div>
        <v-card-title v-if="defenseList.length">
            Registrations
            <v-spacer></v-spacer>
            <v-text-field
                v-if="defenseList.length"
                v-model="search"
                append-icon="search"
                label="Search"
                single-line
                hide-details>
            </v-text-field>
        </v-card-title>
        <v-card-title v-else>
            No Registrations for this charon!
        </v-card-title>

        <v-alert :value="alert" border="left" color="error" outlined>
            <v-row align="center" justify="space-between">
                <v-col class="grow">
                    <md-icon>warning</md-icon>
                    <md-icon>warning</md-icon>
                    <md-icon>warning</md-icon>
                    Are you sure you want to delete this registration?
                    ({{this.item.student_name}}, {{this.item.lab_name}}, {{this.item.choosen_time}})
                    <md-icon>warning</md-icon>
                    <md-icon>warning</md-icon>
                    <md-icon>warning</md-icon>
                </v-col>
                <v-col class="shrink">
                    <v-btn class="ma-2" small tile outlined color="error" @click="deleteRegistration">Yes
                    </v-btn>
                </v-col>
                <v-col class="shrink">
                    <v-btn class="ma-2" small tile outlined color="error" @click="alert=false">No</v-btn>
                </v-col>
            </v-row>
        </v-alert>

        <v-data-table
            v-if="defenseList.length"
            :headers="defense_list_headers"
            :items="defense_list_table"
            :search="search">

            <template v-slot:no-results>
                <v-alert :value="true" color="primary" icon="warning">
                    Your search for "{{ search }}" found no results.
                </v-alert>
            </template>

            <template v-slot:item.teacher="{ item }">
                <v-select
                    class="mx-auto"
                    dense
                    single-line
                    return-object
                    :items="teachers"
                    item-text="fullname"
                    item-value="teacher"
                    v-model="item.teacher"
                    @change="updateRegistration(item.id, item.progress, item.teacher.id)"
                ></v-select>
            </template>

            <template v-slot:item.submission="{ item }">
                <v-btn class="ma-2" small tile outlined color="primary" @click="submissionClicked(item)" block>
                    {{ getSubmissionName(item) }}
                </v-btn>
            </template>

            <template v-slot:item.progress="{ item }">
                <v-select
                    class="mx-auto"
                    dense
                    :items="all_progress_types"
                    v-model="item.progress"
                    @change="updateRegistration(item.id, item.progress, item.teacher.id)"
                ></v-select>
            </template>

            <template v-slot:item.actions="{ item }">
                <v-btn class="ma-2" small tile outlined color="error" @click="promptDeletionAlert(item)">
                    Delete
                </v-btn>
            </template>
        </v-data-table>
    </div>
  </popup-section>
  </div>

</template>

<script>
import {PopupSection} from '../layouts/index'
import Defense from "../../../api/Defense";
import {mapActions, mapState} from "vuex";
import Multiselect from "vue-multiselect";
import Submission from "../../../api/Submission";
import Datepicker from "../../../components/partials/Datepicker";
import moment from "moment";
import {Charon} from "../../../api";
import Teacher from "../../../api/Teacher";

export default {
    name: "CharonDefenseRegistrationsSection",
    components: {Multiselect, PopupSection, Datepicker},
    data() {
        return {
            alert: false,
            item: Object,
            search: '',
            all_progress_types: ['Waiting', 'Defending', 'Done'],
            defense_list_headers: [
                {text: 'Date and time', value: 'choosen_time', align: 'start'},
                {text: 'Lab', value: 'lab_name'},
                {text: 'Student name', value: 'student_name'},
                {text: 'Duration', value: 'formatted_duration'},
                {text: 'Teacher', value: 'teacher'},
                {text: 'Submission', value: 'submission'},
                {text: 'Progress', value: 'progress'},
                {text: 'Actions', value: 'actions'},
            ],
          after: {time: `${moment().format("YYYY-MM-DD")} 00:00`},
          before: {time: null},
          filter_teacher: -1,
          filter_progress: null,
          countDown: 0,
          defenseList: [],
          teachers: []
        }
    },

    created() {
      this.fetchRegistrations()
      VueEvent.$on('refresh-page', this.fetchRegistrations)
      Teacher.getAllTeachers(this.course.id, response => {
        this.teachers = response
      })
    },

    beforeDestroy() {
      VueEvent.$off('refresh-page', this.fetchRegistrations)
    },

    methods: {
      ...mapActions(["updateTeacher"]),

      getSubmissionRouting(submissionId) {
          return '/submissions/' + submissionId
      },

      updateRegistration(defense_id, state, teacher_id) {
          Defense.updateRegistration(this.course.id, defense_id, state, teacher_id, () => {
              VueEvent.$emit('show-notification', "Registration successfully updated", 'danger')
          })
      },

      submissionClicked(submission) {
          if (submission.progress === 'Waiting' && this.isSessionActive) {
              Defense.updateRegistration(this.course.id, submission.id, 'Defending', submission.teacher.id, () => {
              })
          }

          this.$router.push(this.getSubmissionRouting(submission.submission_id))
      },

      promptDeletionAlert(item) {
          this.alert = true
          this.item = item
      },

      deleteRegistration() {
          Defense.deleteStudentRegistration(this.item.charon_id, this.item.student_id, this.item.charon_defense_lab_id, this.item.submission_id, () => {
              VueEvent.$emit('show-notification', "Registration successfully deleted", 'danger')
              this.alert = false
              const index = this.findWithAttr(this.defenseList, "id", this.item.id);
              if (index > -1) {
                  this.defenseList.splice(index, 1);
              }
              this.item = Object
          })
      },

      getFormattedDuration(duration) {
          if (duration === null) {
              return '-'
          }
          return duration + ' min'
      },

      getSubmissionName(submission) {
          let name = "-";
          this.charons.forEach(charon => {
              if (charon.id === submission.charon_id) {
                  name = charon.name
              }
          });
          return name;
      },

      apply() {
        Defense.filtered(this.course.id, this.after.time, this.before.time, this.filter_teacher, this.filter_progress, response => {
          this.defenseList = response
        })
      },

      startSession() {
        const teacher_id = this.findWithAttr(this.teachers, 'id', this.filter_teacher)

        if (teacher_id > -1) {
          const teacher = this.teachers[teacher_id]
          this.updateTeacher({teacher})
          this.apply()
          VueEvent.$emit('show-notification', "Session started", 'danger')
        } else {
          VueEvent.$emit('show-notification', "Please select a teacher", 'danger')
        }
      },

      endSession() {
        const teacher = null
        this.updateTeacher({teacher})
        VueEvent.$emit('show-notification', "Session ended", 'danger')
      },

      findWithAttr(array, attr, value) {
        for (let i = 0; i < array.length; i += 1) {
          if (array[i][attr] === value) {
            return i;
          }
        }
        return -1;
      },

      fetchRegistrations() {
        Defense.filtered(this.course.id, this.after.time, this.before.time, this.filter_teacher, this.filter_progress, response => {
          this.defenseList = response
        })
      },

      getCharon() {
        Charon.getById(this.routeCharonId, response => {
          this.charon = response
        })
        document.title = this.page_name
      },

      fetchSubmissionCounts() {
        Submission.findSubmissionCounts(this.courseId, counts => {
          this.submission_counts = counts.filter(item => item.charon_id === this.routeCharonId).map(item => {
            const container = {};

            container['diff_users'] = item.diff_users;
            container['tot_subs'] = item.tot_subs;
            container['subs_per_user'] = parseFloat(item.subs_per_user).toPrecision(2);
            container['avg_defended_grade'] = parseFloat(item.avg_defended_grade).toPrecision(2);
            container['avg_raw_grade'] = parseFloat(item.avg_raw_grade).toPrecision(2);

            return container;
          });
        })
      },

      fetchLatestSubmissions() {
        Submission.findLatest(this.courseId, submissions => {
          this.latestSubmissions = submissions.filter(submission => submission.charon.id === this.routeCharonId)
        })
      }
    },
    computed: {
        ...mapState([
            'teacher', 'course', 'charons'
        ]),

        isSessionActive() {
            return this.teacher != null
        },

        defense_list_table() {
            return this.defenseList.map(registration => {
                const container = {...registration};
                container['formatted_duration'] = this.getFormattedDuration(registration.defense_duration);
                return container;
            });
        }
    },

}
</script>

<style>

</style>
