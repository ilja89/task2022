<template>

    <popup-section
            title="Tester Types"
            subtitle="Here are all the tester types."
    >

        <v-card class="mx-auto" outlined light raised>
            <v-container class="spacing-playground pa-3" fluid>
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
                                v-model="tester_name"
                                :rules="nameRules"
                                :counter="20"
                                label="Tester type"
                                required
                        ></v-text-field>
                        <v-btn class="ma-2" small tile outlined color="primary"
                               @click="addTesterType(tester_name)">
                            Add
                        </v-btn>
                    </v-col>
                </v-row>
            </v-container>
        </v-card>

    </popup-section>
</template>

<script>
    import {PopupSection} from '../layouts/index'
    import {Course} from "../../../api";

    export default {
        data() {
            return {
                nameRules: [
                    v => !!v || 'Name is required',
                    v => v.length <= 20 || 'Name must be less than 20 characters',
                ],
                tester_name: "",
                alert: false,
                charon_id: 0
            }
        },

        components: {PopupSection},

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
