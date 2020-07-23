<template>
    <div class="defense-row is-flex">
        <div class="defense-field">
            <label>{{ translate('deadline_label') }}</label>
            <p class="input-helper">Defense deadline.</p>
            <datepicker :datetime="form.fields.defense_deadline"></datepicker>
            <input type="hidden" :value="form.fields.defense_deadline">
        </div>

        <div class="defense-field">
            <label>Duration</label>
            <p class="input-helper">Defense duration in minutes.</p>
            <input v-on:click="" type="number"

                   v-model="form.fields.defense_duration"
                   class="form-control">
        </div>

        <div class="defense-field">
            <label v-on:click="show">Labs</label>

            <p class="input-helper">Labs where this Charon can be defended.</p>

            <multiselect v-model="form.defense_labs" :options="labs" :multiple="true" label="name"
                         :close-on-select="false" placeholder="Select labs" trackBy="id"
                         :clear-on-select="true" class="multiselect__width">
            </multiselect>

        </div>

        <div class="defense-field">
            <label>Teacher</label>
            <br>
            <input v-model="form.fields.choose_teacher" type="checkbox">
            <a class="input-helper checkbox-text">Student can choose a teacher.</a>
        </div>

    </div>
</template>

<script>
    import { Datepicker } from '../../../components/partials';
    import { Translate } from '../../../mixins';
    import { CharonSelect } from '../../../components/form';
    import Multiselect from 'vue-multiselect';
    import {Comment} from "../../../api";
    import Lab from "../../../api/Lab";
    import {mapGetters, mapState} from "vuex";

    export default {
        data() {
            return {
                defense: {
                    //deadline: '23-09-2020 23:59',  // actually same as deadline section's deadline, has some more parameters
                    //duration: 8,  // duration in minutes
                    labs: [

                    ],  // list of labs
                    //teacher: true  // boolean - student can choose teacher
                },
                labs: []
            }
        },
        mixins: [ Translate ],

        components: { Datepicker, CharonSelect, Multiselect },

        props: {
            defense: { required: true },
            form: { required: true }
        },
        methods: {
            show() {
                /*Lab.save('1', new Date('2021-12-08 12:00'), new Date('2021-12-08 13:30'), lab => {
                    VueEvent.$emit('show-notification', 'Lab saved!')
                });*/
                console.log(this.defense.labs)
            },
            getDayTimeFormat(start) {
                let daysDict = {0: 'P', 1: 'E', 2: 'T', 3: 'K', 4: 'N', 5: 'R', 6: 'L'};
                return daysDict[start.getDay()] + start.getHours();
            },
            getNiceDate(date) {
                let month = (date.getMonth() + 1).toString();
                if (month.length == 1) {
                    month = "0" + month
                }
                return date.getDate() + '.' + month + '.' + date.getFullYear()
            },
            getLabs() {
                console.log(this.courseId)
                console.log('course id up')
                Lab.all(1, labs => {
                    this.labs = labs
                    this.getNamesForLabs()
                })
            },
            getNamesForLabs() {
                for (let i = 0; i < this.labs.length; i++) {
                    this.labs[i].name = this.getDayTimeFormat(new Date(this.labs[i].start))
                        + ' (' + this.getNiceDate(new Date(this.labs[i].start)) + ')'
                }
            },
        },
        mounted() {
            this.getLabs();
        }
    }
</script>

<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>

<style>

    .datepicker-overlay .cov-date-box .hour-item,
    .datepicker-overlay .cov-date-box .min-item {
        padding: 0 10px;
    }

</style>
