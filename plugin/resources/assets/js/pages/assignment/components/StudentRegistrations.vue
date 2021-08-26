<template>
  <v-card class="mx-auto mb-4">
    <v-card-text class="grey lighten-4">
      <v-container class="spacing-playground pa-3" fluid>
        <v-card-title v-if="registrations.length">
          {{ translate('myRegistrationsText') }}
          <v-spacer></v-spacer>

          <v-text-field
              v-if="registrations.length"
              v-model="search"
              append-icon="search"
              hide-details
              label="Search"
              single-line>
          </v-text-field>
        </v-card-title>

        <v-card-title v-else>
          {{ translate('noRegistrationsText') }}
        </v-card-title>

        <v-layout column style="height: 125vh">
          <v-flex md6 style="overflow: auto">
            <v-data-table
                :charon="charon"
                :headers="headers"
                :items="registrations"
                :registrations="registrations"
                :search="search"
                :student_id="student_id"
                class="elevation-1"
                multi-sort
                single-line>

              <template slot="no-data">
                <v-alert :value="true" style="text-align: center">
                  {{ translate('tableNoRegistrationsText') }}
                </v-alert>
              </template>

              <template v-slot:item.actions="{ item }">
                <v-row dense justify="center" align="center" align-content="center">
                  <v-btn v-if="showDeleteButton(item)" icon @click="deleteItem(item) ">
                    <img alt="delete_item" height="24px" src="pix/bin.png" width="24px">
                  </v-btn>
                  <v-btn v-if="showDeleteButton(item)" icon @click="deferRegistration(item) ">
                    <img alt="defer_item" height="24px" src="pix/later.png" width="24px">
                  </v-btn>
                </v-row>
              </template>
            </v-data-table>
          </v-flex>
        </v-layout>

      </v-container>
    </v-card-text>
  </v-card>
</template>

<script>

import moment from 'moment'
import {Translate} from '../../../mixins';
import Defense from "../../../api/Defense";
import {mapState} from "vuex";

export default {
  mixins: [Translate],

  name: "student-registrations",

  data() {
    return {
      search: '',
      singleSelect: false,
      dialog: false,
      headers: [
        {text: this.translate("charonText"), align: 'start', value: 'name'},
        {text: this.translate("labNameText"), value: 'lab_name'},
        {text: this.translate("timeText"), value: 'choosen_time'},
        {text: this.translate("teacherText"), value: 'teacher'},
        {text: this.translate("locationText"), value: 'teacher_location'},
        {text: this.translate("commentText"), value: 'teacher_comment'},
        {text: this.translate("progressText"), value: 'progress'},
        {text: this.translate("actionsText"), value: 'actions', sortable: false},
      ]
    }
  },

  methods: {
    deferRegistration(item) {
      console.log(this);
      console.log(item);
      const userChoise = prompt(`"get" or "send"? DEBUG!`,"")
      if (userChoise === "send"/*confirm(this.translate("askRegistrationDeferText"))*/) { //Idk how translation system works so pls tell me how to add translation
        Defense.deferStudentRegistration(item, this.student_id, this.charon, (answer) => {
          console.log(answer)
          if (answer.okay === true) {
            VueEvent.$emit('show-notification', this.translate("successfulRegistrationDeferText"), "primary");
            item["reg_id"] = answer.newRegId;
          }
          else {
            VueEvent.$emit('show-notification', this.translate('failedRegistrationDeferText') + answer.reason, 'danger');
          }
        });
      }
      else if(userChoise === "get")
      {
        console.log(item);
        console.log(this);
      }
    },

    deleteItem(item) {
      if (this.dateValidation(item)) {
        if (confirm(this.translate("registrationDeletionConfirmationText"))) {
          this.deleteReg(item);
        }
      } else {
        VueEvent.$emit('show-notification', this.translate("registrationBeforeErrorText"), 'danger')
      }
    },

    dateValidation(item) {

      const today = new Date();
      const date = today.getFullYear() + '-' + (today.getMonth() + 1) + '-' + today.getDate();
      const time = today.getHours() + ":" + today.getMinutes() + ":" + today.getSeconds();
      const dateTime = date + ' ' + time;
      let day1 = moment.utc(dateTime, 'YYYY-MM-DD  HH:mm:ss');
      let day2 = moment.utc(item['lab_start'], 'YYYY-MM-DD  HH:mm:ss');
      return day2.diff(day1, 'hours') >= 2;
    },

    deleteReg(defense_lab_item) {
      Defense.deleteStudentRegistration(this.charon.id, this.student_id, defense_lab_item['defense_lab_id'], defense_lab_item['submission_id'], (xs) => {
        const index = this.registrations.indexOf(defense_lab_item);
        if (index > -1) {
          this.registrations.splice(index, 1)
          VueEvent.$emit('show-notification', 'Deleted ' + xs + ' items successfully!', 'primary')
        }
        this.dialog = false
      })
    },

    showDeleteButton({lab_end,progress})
    {
      if(progress!=="Waiting")
      {
        return false;
      }
      const dateNow = new Date();
      let dateEnd = lab_end.split(" ");
      dateEnd = dateEnd[0].split("-").concat(dateEnd[1].split("-"));
      dateEnd = new Date( dateEnd[0],dateEnd[1]-1,dateEnd[2],dateEnd[3].split(":")[0],dateEnd[3].split(":")[1]);
      if(dateNow.getTime()>dateEnd.getTime())
      {
        return false;
      }
      return true;
    },

    getDefenseData() {
      Defense.getDefenseData(this.charon_id, this.student_id, (data) => {
        this.$store.state.registrations = data;
      })
    }
  },

  computed: {
    ...mapState([
      'registrations',
      'student_id',
      'charon'
    ]),
  },

}
</script>

<style>

.v-application--wrap {
    min-height: 1vh !important;
}

</style>
