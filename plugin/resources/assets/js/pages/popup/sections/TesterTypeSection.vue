<template>

    <div class="lab">

        <v-card
                class="mx-auto"
                outlined
                light
                raised
                shaped
        >
            <v-container
                    class="spacing-playground pa-3"
                    fluid
            >
                <v-row>
                    <v-col>
                        <table class="table  is-fullwidth  is-striped  submission-counts__table">
                            <thead>
                            <tr>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="tester in testerTypes">
                                <td>{{tester.code}}</td>
                                <td>{{tester.name}}</td>
                                <td>
                                    <v-btn class="ma-2" small tile outlined color="error"
                                           @click="deleteTesterType(tester.name)">
                                        Delete
                                    </v-btn>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </v-col>
                    <v-col>
                        <v-text-field
                                v-model="firstname"
                                :rules="nameRules"
                                :counter="20"
                                label="First name"
                                required
                        ></v-text-field>
                        <v-btn class="ma-2" small tile outlined color="primary"
                               @click="addTesterType(firstname)">
                            Add
                        </v-btn>
                    </v-col>
                </v-row>
            </v-container>
        </v-card>

    </div>
</template>

<script>
    import {mapActions} from "vuex";
    import {Course} from "../../../api";

    export default {
        data() {
            return {
                nameRules: [
                    v => !!v || 'Name is required',
                    v => v.length <= 20 || 'Name must be less than 10 characters',
                ],
                tester_name: "",
                alert: false,
                charon_id: 0
            }
        },
        props: {
            testerTypes: {required: true},
            courseId: {required: true}
        },
        methods: {

            editClicked(charon) {
                this.updateCharon({charon});
                window.location = 'popup#/defSettingsEditing'
            },

            deleteTesterType(testerName) {
                Course.removeTesterType(this.courseId, testerName, done => {
                    window.location.reload();
                })
            },

            addTesterType(testerName) {
                Course.addTesterType(this.courseId, testerName, done => {
                    window.location.reload();
                })
            }
        },
    }
</script>
