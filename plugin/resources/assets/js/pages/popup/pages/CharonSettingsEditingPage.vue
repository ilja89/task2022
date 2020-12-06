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

                    <v-btn class="ma-2" small tile outlined color="primary"
                           @click="saveClicked">
                        Save
                    </v-btn>

                    <v-btn class="ma-2" small tile outlined color="error" @click="cancelClicked">
                        Cancel
                    </v-btn>

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
    methods: {
        saveClicked() {
            try {
                Charon.saveCharon(this.charon, () => {
                    window.location = "popup#/charonSettings";
                    window.location.reload();
                    VueEvent.$emit('show-notification', 'Charon settings successfully updated!')
                })
            } catch (e) {
                console.log(e)
                VueEvent.$emit('show-notification', 'Make sure start time and deadline are filled!')
            }
        },

        cancelClicked() {
            window.location = "popup#/charonSettings";
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