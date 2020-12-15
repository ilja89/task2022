<template>

    <popup-section
            title="Tester Types"
            subtitle="Here are all the tester types."
    >
        <v-row>
            <v-col cols="12" sm="12" md="8" lg="8">
                <v-data-table
                        hide-default-footer
                        disable-pagination
                        :headers="tester_type_headers"
                        :items="testerTypes">
                    <template v-slot:item.actions="{ item }">
                        <v-btn class="ma-2" small tile outlined color="error"
                               @click="deleteTesterType(item.name)">
                            Delete
                        </v-btn>
                    </template>
                </v-data-table>
            </v-col>
            <v-col cols="12" sm="12" md="4" lg="4">
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

    </popup-section>
</template>

<script>
    import {PopupSection} from '../layouts/index'
    import {Course} from "../../../api";
    import {mapState} from "vuex";

    export default {
        data() {
            return {
                nameRules: [
                    v => !!v || 'Name is required',
                    v => v.length <= 20 || 'Name must be less than 20 characters',
                ],
                tester_type_headers: [
                    {text: 'Code', value: 'code', align: 'start'},
                    {text: 'Name', value: 'name'},
                    {text: 'Actions', value: 'actions'},
                ],
                tester_name: "",
                alert: false,
                charon_id: 0,
                testerTypes: []
            }
        },
        components: {PopupSection},

        props: {
            courseId: {required: true}
        },

        methods: {
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

        computed: {
            ...mapState([
                'course'
            ]),
        },

        created() {
            Course.getTesterTypes(this.course.id, response => {
                this.testerTypes = response
            })
        }
    }
</script>
