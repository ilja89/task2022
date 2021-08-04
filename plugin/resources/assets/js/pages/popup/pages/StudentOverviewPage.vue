<template>
    <div class="student-overview-container">

      <v-card class="mb-16 pl-4">
        <v-card-title>Student overview</v-card-title>
      </v-card>

        <popup-section title="Students"
                       subtitle="Here is a list of all students for this course.">

              <div v-for="(student, index) in students" :key="index">
                <div class="card hover-overlay submission" @click="studentDetails(student.id)">{{ student.name }} </div>
              </div>

        </popup-section>
    </div>
</template>

<script>
    import {PopupSection} from '../layouts'

    export default {

        components: {PopupSection},

        data() {
            return {
                students: [
                    {name: 'Paula Php', uniId: 'paphp', id: 3},
                    {name: 'Jaak Java', uniId: 'jjava', id: 33},
                    {name: 'Stiina Siisharp', uniId: 'ssiis', id: 69}
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
