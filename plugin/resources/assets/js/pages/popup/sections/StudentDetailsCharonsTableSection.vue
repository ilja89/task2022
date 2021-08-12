<template>
  <popup-section title="Charons' details"
                 subtitle="Here's some info on charons for this student.">
    <v-card-title v-if="table.length">
      Charons
      <v-spacer></v-spacer>
      <v-text-field
          v-if="table.length"
          v-model="search"
          append-icon="search"
          label="Search"
          single-line
          hide-details>
      </v-text-field>
    </v-card-title>
    <v-card-title v-else>
      No Charons for this student!
    </v-card-title>

    <v-data-table
        v-if="table.length"
        :headers="table_headers"
        :items="table"
        :search="search">
      <template v-slot:no-results>
        <v-alert :value="true" color="primary" icon="warning">
          Your search for "{{ search }}" found no results.
        </v-alert>
      </template>
      <template v-slot:item.defended="{ item }">
        <v-chip :color="getDefColor(item.defended)" dark> {{ item.defended }}</v-chip>
      </template>
      <template v-slot:item.studentPoints="{ item }">
        <v-chip :color="getPtsColor(item.maxPoints, item.studentPoints)" dark> {{ item.studentPoints }}</v-chip>
      </template>
    </v-data-table>

  </popup-section>

</template>

<script>
import {PopupSection} from '../layouts/index'
import Charon from "../../../api/Charon";

export default {
  name: "StudentDetailsCharonsTableSection",

  data() {
    return {
      alert: false,
      search: '',
      table_headers: [
        {text: 'Charon', value: 'name', align: 'start'},
        {text: 'Max Points', value: 'maxPoints'},
        {text: 'Student Points', value: 'studentPoints'},
        {text: 'Defended', value: 'defended'}
      ]
    }
  },

  props: ['table'],

  components: {PopupSection},

  methods: {
    getDefColor (defended) {
      if (defended === 'Yes') return 'green'
      else return 'red'
      },

    getPtsColor (maxPoints, studentPoints) {
      if (studentPoints === maxPoints) return 'green'
      else if (studentPoints < maxPoints && studentPoints > 0) return 'orange'
      else return 'red'
      }
    }
}
</script>

<style scoped>

</style>