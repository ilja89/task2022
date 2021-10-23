<template>
    <!--Moodle drawer has z-index under 1000-->
    <v-bottom-sheet v-model="sheet" inset persistent style="position: relative; z-index: 1000">
        <template v-slot:activator="{ on, attrs }">
            <v-btn v-if="hasPoints && submissionStyleOK" v-bind="attrs" v-on="on" icon @click="sheet=true">
                <img alt="shield" height="24px" src="pix/shield.png" width="24px">
            </v-btn>

            <v-btn v-else icon @click="notify">
                <img alt="shield" height="24px" src="pix/shield.png" width="24px">
            </v-btn>
        </template>

        <div>
            <v-toolbar :color="color" dark>
                <span class="headline">{{ translate('registrationForText') }} {{ this.charon['name'] }}</span>

                <v-spacer></v-spacer>

                <v-btn color="error" @click="sheet = false">
                    {{ translate('closeText') }}
                </v-btn>
            </v-toolbar>

            <v-sheet style="position:relative;" class="px-4 pt-4" height="80vh">
                <div class="register-lab-headers" style="margin-top: 2vh">
                    <h4>{{ translate('chooseTimeText') }}</h4>
                </div>
                <div class="labs-schedule">
                    <div class="text-center">
                        <multiselect v-model="value" :allow-empty="false" :block-keys="['Tab', 'Enter']"
                                     :custom-label="getLabList" :max-height="200"
                                     :options="this.labs" :placeholder="translate('selectDayText')"
                                     label="start"
                                     track-by="id" @select="onSelect">
                            <template slot="singleLabel" slot-scope="{ option }">{{ option | getLabList }}</template>
                        </multiselect>
                    </div>
                </div>

                <v-row class="mt-4">
                    <v-btn :disabled="!value || value && !value['estimated_start_time']"
                        class="ml-4" color="primary" dense outlined text @click="sendData()">
                        {{ translate('registerText') }}
                    </v-btn>

                    <v-btn class="ml-4" color="error" dense outlined text @click="sheet = false">
                        {{ translate('closeText') }}
                    </v-btn>
                </v-row>

                <loading-container :render="this.busy"></loading-container>

            </v-sheet>
        </div>
    </v-bottom-sheet>

</template>

<script>
import {Multiselect} from "vue-multiselect";
import {Translate} from "../../../mixins";
import {mapState} from "vuex";
import {getSubmissionWeightedScore} from "../helpers/submission";
import LoadingContainer from "../graphics/LoadingContainer";
import getLabList from "../../../filters/getLabList";
import {Defense} from "../../../api";

export default {

    mixins: [Translate],

    components: {
        LoadingContainer,
        Multiselect
    },

    props: {
        submission: {required: true},
        color: {required: true}
    },

    name: "registration-bottom-sheet",

    data() {
        return {
            hasPoints: false,
            submissionStyleOK: true,
            sheet: false,
            cached_option: null,
            value: null,
            busy: false
        }
    },

    filters: {
        getLabList
    },

    computed: {
        ...mapState([
            'charon_id',
            'student_id',
            'registrations',
            'charon',
            'labs'
        ]),
    },


    methods: {
        getDefenseData() {
            axios.get(`api/charons/${this.charon_id}/registrations?id=${this.charon_id}&user_id=${this.student_id}`).then(result => {
                this.$store.state.registrations = result.data
            })
        },

        notify() {
            let submissionWeightedScore = getSubmissionWeightedScore(this.submission);

            if (!this.hasPoints) {
                VueEvent.$emit('show-notification', `You can't register a submission with a result ${submissionWeightedScore}%, as it's less than ${this.charon['defense_threshold']}%`, 'danger')
            }

            if (!this.submissionStyleOK) {
                VueEvent.$emit('show-notification', `Please fix your style before registering to submission`, 'danger')
            }
        },

        sendData() {
            if (this.value !== null) {
                this.busy = true;
                Defense.register(this.charon.id, this.student_id, this.value['defense_lab_id'],
                    this.submission.id, null, () => {
                        VueEvent.$emit('show-notification', "Registration was successful!", 'primary');
                        this.isActive = false;
                        this.endLoading();
                    });
            } else {
                VueEvent.$emit('show-notification', "Needed parameters weren't inserted!", 'danger');
            }
        },

        endLoading() {
            this.getDefenseData();
            this.busy = false;
        },

    // filter imported above, used as method too, because for "custom-label" function is required.
    getLabList,

        onSelect(option) {
            this.cached_option = option;
        },
    },

    created() {
        this.submissionStyleOK = true
        for (let j = 0; j < this.submission.results.length; j++) {
            const code = parseInt(this.submission.results[j].grade_type_code);
            if (code > 100 && code <= 1000) {
                const result = parseFloat(this.submission.results[j].calculated_result);
                if (result < 0.999) {
                    this.submissionStyleOK = false
                }
            }
        }

        this.hasPoints = getSubmissionWeightedScore(this.submission) >= this.charon['defense_threshold'];
        VueEvent.$on('student-register-end-loading', this.endLoading);
    },

    beforeDestroy() {
        VueEvent.$off('student-register-end-loading');
    },
}
</script>
