<template>
  <div>
      <v-card class="mx-auto mb-16" outlined light raised>
        <v-container class="spacing-playground pa-3" fluid>
          <v-form>
            <v-container>
              <v-row>
                <v-col>
                  <div class="helper">Lab</div>
                  <v-select placeholder="Lab"
                            :items="labs"
                            item-text="name"
                            v-model="item.lab"
                            return-object
                            @change="updateFields"
                  ></v-select>
                </v-col>
                <v-col>
                  <div class="helper">Student</div>
                  <v-select placeholder="Student"
                            :items="students"
                            item-text="fullname"
                            v-model="item.student"
                            return-object
                  ></v-select>
                </v-col>
                <v-col>
                  <div class="helper">Charon</div>
                  <v-select placeholder="Charon"
                            :items="charons"
                            item-text="project_folder"
                            v-model="item.charon"
                            return-object
                  ></v-select>
                </v-col>
                <v-col>
                  <div class="helper">Progress</div>
                  <v-select placeholder="Progress"
                            class="mx-auto"
                            v-model="default_progress"
                            :items="all_progress_types"
                  ></v-select>
                </v-col>
              </v-row>
              <v-row>
                <v-btn class="ma-2" small tile outlined color="primary"  @click="Save">
                  Save
                </v-btn>
                <v-btn class="ma-2" small tile outlined color="error" @click="Cancel">
                  Cancel
                </v-btn>
              </v-row>
            </v-container>
          </v-form>
        </v-container>
      </v-card>
  </div>
</template>

<script>

  import router from "../routes";
  import {Lab, User} from "../../../api";
  import {mapState} from "vuex";

  export default {
    data: function () {
      return {
        all_progress_types: ['Waiting', 'Defending', 'Done'],
        default_progress: 'Waiting',
        labs: [],
        students: [],
        charons: [],
        item: Object
      }
    },
    computed: {
      ...mapState([
        'course'
      ]),
    },
    methods: {
      Cancel(){
        router.go(-1)
      },
      Save(){
        router.go(-1)
      },

      updateFields() {
        if (this.item.lab) {
          this.charons = this.item.lab.charons;
        }
      }
    },

    created() {
      Lab.all(this.course.id, (response) => {
        this.labs = response;
      });

      User.allStudents(this.course.id, (response) => {
        this.students = response;
      })
    }
  }
</script>
<style>

</style>
