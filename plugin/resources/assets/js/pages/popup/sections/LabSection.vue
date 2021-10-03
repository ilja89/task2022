<template>
  <popup-section
      title="Labs overview"
      subtitle="Here are the the labs where students can show their code.">
    <template slot="header-right">
      <v-btn class="ma-2" tile outlined color="primary" v-on:click="addNewLabSessionClicked">Add new</v-btn>
    </template>

    <v-alert :value="alert" border="left" color="error" outlined>
      <v-row align="center" justify="space-between">
        <v-col class="grow">
          <md-icon>warning</md-icon>
          <md-icon>warning</md-icon>
          <md-icon>warning</md-icon>
          Are you sure you want to delete the lab?
          <md-icon>warning</md-icon>
          <md-icon>warning</md-icon>
          <md-icon>warning</md-icon>
        </v-col>
        <v-col class="shrink">
          <v-btn class="ma-2" small tile outlined color="error" @click="deleteLab">Yes</v-btn>
        </v-col>
        <v-col class="shrink">
          <v-btn class="ma-2" small tile outlined color="error" @click="alert=false">No</v-btn>
        </v-col>
      </v-row>
      <v-row class="lab-overview-message">
        <div v-if="registrations === -1">
          Checking if the Lab has registrations ...
        </div>
        <div v-else-if="registrations === 0" class="green-text">
          Lab has no registrations
        </div>
        <div v-else>
          Lab has {{ registrations }} registrations
        </div>
      </v-row>
    </v-alert>
    <v-card-title v-if="labs.length">
      Labs
      <v-spacer></v-spacer>
      <div class="subtitle-1">
        Start date &nbsp;&nbsp;
      </div>
      <div class="subtitle-1">
        <datepicker :datetime="start_date" :date_only=true></datepicker>
      </div>
      <v-spacer></v-spacer>
      <v-text-field
          v-if="labs.length"
          v-model="search"
          append-icon="search"
          label="Search"
          single-line
          hide-details>
      </v-text-field>
    </v-card-title>

    <v-card-title v-else>
      No Labs for this course!
    </v-card-title>

    <v-data-table
        id="lab-overview-headers"
        v-if="labs.length"
        :headers="computedHeaders"
        :items="labs_table"
        :search="search">

      <template v-slot:item.actions="{ item }">
        <v-btn class="ma-2" small tile outlined color="primary" @click="editLabClicked(item)">Edit
        </v-btn>
        <v-btn class="ma-2" small tile outlined color="error" @click="promptDeletionAlert(item)">
          Delete
        </v-btn>
      </template>
      <template v-slot:no-results>
        <v-alert :value="true" color="primary" icon="warning">
          Your search for "{{ search }}" found no results.
        </v-alert>
      </template>
    </v-data-table>

  </popup-section>
</template>

<style lang="scss">
#lab-overview-headers {
  .v-data-table__mobile-row {
    min-height: 48px;
    height: auto;
  }
}

.lab-overview-message div {
  margin-left: 16px;

  &.green-text {
    color: green;
  }
}
</style>

<script>
import {PopupSection} from '../layouts/index'
import {mapActions, mapState} from "vuex";
import Lab from "../../../api/Lab";
import CharonFormat from "../../../helpers/CharonFormat";
import moment from "moment";
import Datepicker from "../../../components/partials/Datepicker";
import _ from "lodash";

export default {
  name: "lab-section",

  components: {PopupSection, Datepicker},

  props: {
    labs: {required: true},
    activityDashboardPage: {
      required: false,
      default: false,
      type: Boolean
    }
  },

  data() {
    return {
      alert: false,
      lab_id: 0,
      registrations: -1,
      search: '',
      start_date: {time: `${moment().format("YYYY-MM-DD")}`},
      labs_headers: [
        {text: 'Name', value: 'nice_name', align: 'start'},
        {text: 'Date', value: 'nice_date'},
        {text: 'Time', value: 'nice_time'},
        {text: 'Teachers', value: 'teacher_names'},
        {text: 'Charons', value: 'charon_names'},
        {text: 'Actions', value: 'actions'},
      ],
      previous_param: null,
      current_param: null
    }
  },

  computed: {

    ...mapState([
      'course'
    ]),

    computedHeaders() {
      return this.activityDashboardPage ? [
        {text: 'Name', value: 'nice_name', align: 'start'},
        {text: 'Date', value: 'nice_date'},
        {text: 'Time', value: 'nice_time'},
        {text: 'Teachers', value: 'teacher_names'},
        {text: 'Actions', value: 'actions'},
      ] : this.labs_headers
    },

    labs_table() {
      let startDate = moment(this.start_date.time).valueOf();
      return this.labs.reduce((r, lab) => {
        if (+moment(lab.start.time) < startDate) return r;

        const container = {...lab};
        container['nice_name'] = lab.name ? lab.name : CharonFormat.getDayTimeFormat(lab.start.time);
        container['nice_date'] = CharonFormat.getNiceDate(lab.start.time);
        container['nice_time'] = `${CharonFormat.getNiceTime(lab.start.time)} - ${CharonFormat.getNiceTime(lab.end.time)}`;
        container['teacher_names'] = lab.teachers.map(x => x.fullname).sort().join(', ')
        if (!this.activityDashboardPage) {
          container['charon_names'] = lab.charons.map(x => x.project_folder).sort().join(', ')
        }

        r.push(container);
        return r;
      }, []);
    }
  },

  methods: {
    ...mapActions(["updateLab", "updateLabToEmpty"]),

    addNewLabSessionClicked() {
      this.updateLabToEmpty()
      window.location = "popup#/labsForm";
    },

    editLabClicked(lab) {
      this.updateLab({lab: _.cloneDeep(lab)})
      window.location = "popup#/labsForm";
    },

    promptDeletionAlert(lab) {
      this.alert = true
      this.lab_id = lab.id
      this.registrations = -1;
      Lab.checkRegistrations(this.course.id, this.lab_id, {}, (result) => {
        this.registrations = result;
      });
    },

    deleteLab() {
      this.alert = false
      Lab.delete(this.course.id, this.lab_id, () => {
        this.labs = this.labs.filter(x => x.id !== this.lab_id)
        VueEvent.$emit('show-notification', 'Lab deleted!')
      })
    },
  }
}

</script>
