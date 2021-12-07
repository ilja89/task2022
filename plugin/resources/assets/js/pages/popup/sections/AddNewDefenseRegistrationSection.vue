<template>
  <div>
      <v-card class="mx-auto mb-16" outlined light raised>
        <v-container class="spacing-playground pa-3" fluid>
          <v-form>
            <v-container>
              <v-row>
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
                            item-text="name"
                            v-model="item.charon"
                            return-object
                            @change="updateFields"
                  ></v-select>
                </v-col>
                <v-col>
                  <div class="helper">Lab</div>
                  <v-select placeholder="Lab"
                            :items="labs"
                            item-text="name"
                            v-model="item.lab"
                            return-object
                  ></v-select>
                </v-col>
              </v-row>
              <v-row>
                <v-btn class="ma-2" small tile outlined color="primary" @click="save">
                  Save
                </v-btn>
                <v-btn class="ma-2" small tile outlined color="error" @click="leave">
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
  import {Charon, Defense, User} from "../../../api";
  import {mapState} from "vuex";

  export default {

    data: function () {
      return {
        labs: [],
        students: [],
        charons: [],
        item: {}
      }
    },
    computed: {
      ...mapState([
        'course'
      ]),
    },
    methods: {
      leave() {
        router.go(-1);
      },

      save() {
        if (this.item && this.item.lab && this.item.student && this.item.charon) {
          Defense.registerByTeacher(this.item.charon.id, this.item.student.id, this.item.lab.defense_lab_id,
              () => {
              VueEvent.$emit('show-notification', "Registration was successful!", 'primary');
              router.push('defenseRegistrations');
              VueEvent.$emit('refresh-page');
            });
        } else {
          VueEvent.$emit('show-notification', "Needed fields were not filled!", 'danger');
        }
      },

      updateFields() {
        if (this.item.charon) {
          this.labs = this.item.charon.defense_labs;
          if (this.item.lab) {
            this.item.lab = null;
          }
        }
      },
    },

    created() {
      Charon.all(this.course.id, (response) => {
        this.charons = response;
      });

      User.allStudents(this.course.id, (response) => {
        this.students = response;
      });
    }
  }
</script>
<style>

</style>
