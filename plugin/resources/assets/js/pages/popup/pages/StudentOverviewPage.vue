<template>
    <div class="student-overview-container">

      <v-card class="mb-16 pl-4">
        <v-card-title>Student overview</v-card-title>
      </v-card>

      <popup-section title="Students"
                     subtitle="Here is a list of all students for this course.">

        <v-card-title v-if="students.length">
          Students
          <v-spacer></v-spacer>
          <v-text-field
              v-if="students.length"
              v-model="search"
              append-icon="search"
              label="Search"
              single-line
              hide-details>
          </v-text-field>
        </v-card-title>
        <v-card-title v-else>
          No Students for this course!
        </v-card-title>

        <v-data-table
            v-if="students.length"
            :headers="students_headers"
            :items="students"
            :search="search">
          <template v-slot:no-results>
            <v-alert :value="true" color="primary" icon="warning">
              Your search for "{{ search }}" found no results.
            </v-alert>
          </template>
          <template v-slot:item.actions="{ item }">
            <v-btn class="ma-2" small tile outlined color="primary" @click="studentDetails(item.id)">Details
            </v-btn>
          </template>
        </v-data-table>

      </popup-section>

    </div>
</template>

<script>
    import {PopupSection} from '../layouts'

    export default {

        components: {PopupSection},

        data() {
            return {
              alert: false,
              search: '',
              students: [
                {name: 'Kaia Kaalikas', uniId: 'kkaal', id: 3},
                {name: 'Jaak Java', uniId: 'jjava', id: 4},
                {name: 'Stiina Siisharp', uniId: 'ssiis', id: 5},
                {name: 'Paul Pyyton', uniId: 'ppyyt', id: 6},
                {name: 'Paula Php', uniId: 'paphp', id: 7},
                {name: 'Place Holder', uniId: 'phold', id: 8},
                {name: 'Place Holder', uniId: 'phold', id: 9},
                {name: 'Place Holder', uniId: 'phold', id: 10},
                {name: 'Place Holder', uniId: 'phold', id: 11},
                {name: 'Place Holder', uniId: 'phold', id: 12},
                {name: 'Place Holder', uniId: 'phold', id: 13},
                {name: 'Place Holder', uniId: 'phold', id: 14},
                {name: 'Place Holder', uniId: 'phold', id: 15},
                {name: 'Place Holder', uniId: 'phold', id: 16},
                {name: 'Place Holder', uniId: 'phold', id: 17},
              ],
              students_headers: [
                {text: 'Full name', value: 'name', align: 'start'},
                {text: 'Uni-id', value: 'uniId'},
                {text: 'Actions', value: 'actions'}
              ]
            }
        },

        methods: {
            studentDetails(id) {
              this.$router.push({ name: 'student-details', params: {student_id: `${id}`} })
            }
        }
    }
</script>

<style lang="scss">

    .student-overview-card {
        padding: 25px;
    }

    .b1l {
        padding: 0;
        width: 24px;
        min-width: 24px;
    }

    .student-overview-card {
        th {
            padding: 0.75em;

            img {
                margin-right: 15px;
            }
        }

        td {
            padding: 0.75em;
        }
    }

</style>
