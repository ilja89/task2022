<template>
    <div id="app">
        <v-app id="inspire" class="inspire">
            <v-data-table
                    :headers="headers"
                    :items="defenseData"
                    sort-by="calories"
                    class="elevation-1"
                    :hide-default-footer="true"
                    single-line
                    :defenseData="defenseData"
                    :student_id="student_id"
                    :charon="charon"
            >
                <template slot="no-data">
                    <v-alert :value="true" style="text-align: center">
                        Sorry, nothing to display here :(
                    </v-alert>
                </template>
                <template v-slot:top>
                    <v-toolbar flat color="white" height="0 px">

                        <v-dialog v-model="dialog" max-width="500px">
                            <template v-slot:activator="{ on, attrs }">

                            </template>
                            <v-card>
                                <v-card-title>
                                    <span class="headline">{{ formTitle }}</span>
                                </v-card-title>

                                <v-card-text>
                                    <v-container>
                                        <v-row>
                                            <v-col cols="12" sm="6" md="4">
                                                <v-text-field v-model="editedItem.time" label="Defense time"></v-text-field>
                                            </v-col>
                                            <v-col cols="12" sm="6" md="4">
                                                <v-text-field v-model="editedItem.teacher" label="Teacher for defense"></v-text-field>
                                            </v-col>
                                        </v-row>
                                    </v-container>
                                </v-card-text>

                                <v-card-actions>
                                    <v-spacer></v-spacer>
                                    <v-btn color="blue darken-1" text @click="close">Cancel</v-btn>
                                    <v-btn color="blue darken-1" text @click="save">Save</v-btn>
                                </v-card-actions>
                            </v-card>
                        </v-dialog>
                    </v-toolbar>
                </template>
                <template v-slot:item.actions="{ item }">
                    <i @click="deleteItem(item)" class="fa fa-trash fa-lg" aria-hidden="true"></i>
                </template>
            </v-data-table>
        </v-app>
    </div>
</template>

<script>
    export default {
        props: {
            defenseData: {required: true},
            student_id: {required: true},
            charon: {required: true},
        },
        name: "StudentDefenses",
        data() {
            return {
                singleSelect: false,
                dialog: false,
                headers: [
                    {
                        text: 'Charon',
                        align: 'start',
                        sortable: false,
                        value: 'name',
                    },
                    { text: 'Time', value: 'choosen_time' },
                    { text: 'Teacher', value: 'teacher' },
                    { text: 'Actions', value: 'actions', sortable: false },
                ],
                editedIndex: -1,
                editedItem: {
                    name: '',
                    choosen_time: '',
                    teacher: ''
                },
                defaultItem: {
                    name: '',
                    choosen_time: '',
                    teacher: 'Another teacher'
                },
            }
        },
        methods: {
            deleteItem(item) {
                const index = this.defenseData.indexOf(item);
                if (this.dateValidation(item)) {
                    if (confirm('Are you sure you want to delete this item?') && this.defenseData.splice(index, 1)) {
                        this.deleteReg(item['defense_lab_id']);
                    }
                } else alert("You can't delete a registration 2 hours before the start!");
            },

            dateValidation (item) {
                const today = new Date();
                const date = today.getFullYear()+'-'+(today.getMonth()+1)+'-'+today.getDate();
                const time = today.getHours() + ":" + today.getMinutes() + ":" + today.getSeconds();
                const dateTime = date +' '+ time;
                let day1 = moment.utc(dateTime, 'YYYY-MM-DD  HH:mm:ss');
                let day2 = moment.utc(item['choosen_time'], 'YYYY-MM-DD  HH:mm:ss');
                return day2.diff(day1, 'hours') >= 2;
            },

            deleteReg(defLab_id) {
                axios.delete(`api/delete_defense.php?student_id=${this.student_id}&defLab_id=${defLab_id}&charon_id=${this.charon['id']}`)
            },

            close () {
                this.dialog = false
                this.$nextTick(() => {
                    this.editedItem = Object.assign({}, this.defaultItem)
                    this.editedIndex = -1
                })
            },

            save () {
                if (this.editedIndex > -1) {
                    Object.assign(this.defenseData[this.editedIndex], this.editedItem)
                } else {
                    this.defenseData.push(this.editedItem)
                }
                this.close()
            },
        },

        computed: {
            formTitle () {
                return this.editedIndex === -1 ? 'New Item' : 'Edit Item'
            },
        },

    }
</script>

<style scoped>

</style>