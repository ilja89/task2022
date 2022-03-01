<template>
    <div>
        <v-card class="mb-16 pl-4">
            <v-card-title>Edit Charon settings</v-card-title>
        </v-card>

        <popup-section title="Charon Settings"
                       subtitle="Here are the general settings for each charon.">
            <v-card class="mx-auto" outlined light raised>
                <v-container class="spacing-playground pa-3" fluid>

                    <charon-settings-form :charon="charon" :course_id="course.id"/>

                    <v-btn class="ma-2" small tile outlined color="primary" @click="saveClicked">
                        Save
                    </v-btn>

                    <v-btn class="ma-2" small tile outlined color="error" @click="cancelClicked">
                        Cancel
                    </v-btn>

                    <v-dialog v-model="retestConfirmation" persistent max-width="600">
                        <template v-slot:activator="{ on, attrs }">
                            <v-btn class="ma-2 float-right" small tile outlined color="warning" v-bind="attrs" v-on="on">
                                Retest all submissions
                            </v-btn>
                        </template>
                        <v-card>
                            <v-card-title class="headline">
                                Are you sure you?
                            </v-card-title>
                            <v-card-text>
                                Every student will have their latest Submission connected to this Charon retested.
                                This will take some time depending on the number of Submissions and how busy the tester is.
                            </v-card-text>
                            <v-card-actions>
                                <v-spacer></v-spacer>
                                <v-btn color="red darken-1" outlined text @click="retestConfirmation = false">No</v-btn>
                                <v-btn color="green darken-1" outlined text @click="retest()">Yes</v-btn>
                            </v-card-actions>
                        </v-card>
                    </v-dialog>

                </v-container>
            </v-card>
        </popup-section>
    </div>
</template>

<script>
import {PopupSection} from '../layouts/index'
import {mapState} from "vuex";
import Charon from "../../../api/Charon";
import CharonSettingsForm from "../sections/CharonSettingsForm";

export default {
    name: "charon-settings-editing-page",

    components: {CharonSettingsForm, PopupSection},

    data () {
        return {
            retestConfirmation: false
        }
    },

    methods: {
        saveClicked() {
            try {
                Charon.saveCharon(this.charon, () => {
                    this.$router.go(-1)
                    VueEvent.$emit('show-notification', 'Charon settings successfully updated!')
                })
            } catch (e) {
                VueEvent.$emit('show-notification', 'Make sure start time and deadline are filled!')
            }
        },

        cancelClicked() {
            this.$router.go(-1)
        },

        retest() {
            this.retestConfirmation = false;
            Charon.retestSubmissions(this.charon.id, (response) => {
                window.VueEvent.$emit('show-notification', response.message)
            });
        },
    },

    computed: {
        ...mapState([
            'charon',
            'charons',
            'course'
        ]),
    },

    created() {
        if (this.charons == null || this.charon == null) {
            Charon.all(this.course.id, response => {
                this.$store.state.charons = response
                response.forEach(charon => {
                    if (charon.id.toString() === this.$route.params.charon_id) {
                        this.$store.state.charon = charon
                    }
                });
            })
        }
    }
}
</script>