<template>
    <div>
        <div class="helper">
            After
        </div>
        <div class="datepick">
            <datepicker :datetime="after"></datepicker>
            <input type="hidden" :value="after">
        </div>
        <div class="helper">
            Before
        </div>
        <div class="datepick">
            <datepicker :datetime="before"></datepicker>
            <input type="hidden" :value="before">
        </div>
        <div class="apply-btn-container">
            <button class="btn-apply" v-on:click="apply(after.time, before.time)">Apply</button>
        </div>
        <div class="card  has-padding">
            <table class="table  is-fullwidth  is-striped  submission-counts__table">
                <thead>
                <tr>
                    <th>
                        Date and time
                    </th>
                    <th>
                        Student name
                    </th>
                    <th>
                        Duration
                    </th>
                    <th>
                        Teacher
                    </th>
                    <th>
                        Submission
                    </th>
                    <th>
                        Progress
                    </th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="defense in defenseList">
                    <td>{{defense.choosen_time}}</td>
                    <td>{{defense.student_name}}</td>
                    <td>{{getFormattedDuration(defense.defense_duration)}}</td>
                    <td>{{defense.teacher.firstname}} {{defense.teacher.lastname}}</td>
                    <td><router-link :to="getSubmissionRouting(defense.submission_id)">Go to submission</router-link></td>
                    <td>
                        <div class="dropdown">
                            <button class="dropbtn">{{defense.progress}}</button>
                            <div id="dropdown-content" class="dropdown-content">
                                <a v-on:click="saveProgress(defense.id, 'Waiting')">Waiting</a>
                                <a v-on:click="saveProgress(defense.id, 'Defending')">Defending</a>
                                <a v-on:click="saveProgress(defense.id, 'Done')">Done</a>
                            </div>
                        </div>

                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script>
    import Datepicker from "../../../components/partials/Datepicker";
    import Defense from "../../../api/Defense";
    import {mapState} from "vuex";

    export default {
        components: {Datepicker},
        data() {
            return {
                after: {time: null},
                before: {time: null},
            }
        }, props: {
            defenseList: {required: true},
            apply: {required: true}
        },
        methods: {
            getSubmissionRouting(submissionId) {
                return '/submissions/' + submissionId
            },
            saveProgress(defenseId, state) {
                Defense.saveDefenseProgress(this.course.id, defenseId, state,() => {
                    for (let i = 0; i < this.defenseList.length; i++) {
                        if (this.defenseList[i].id === defenseId) {
                            this.defenseList[i].progress = state
                            break
                        }
                    }
                })
            },
            getFormattedDuration(duration) {
                if (duration === null) {
                    return '-'
                }
                return duration + ' min'
            }
        },
        computed: {
            ...mapState([
                'course'
            ]),
        }
    }
</script>

<style>

    .datepicker-overlay .cov-date-box .hour-item,
    .datepicker-overlay .cov-date-box .min-item {
        padding: 0 10px;
    }

    .helper {
        font-size: 1.2rem;
        font-weight: 400;
        color: #1177d1;
    }

    .datepick {
        padding-bottom: 14px;
    }

    .btn-apply {
        cursor: pointer;
        border-style: none;
        margin: 2px;
        display: inline-block;
        font-weight: 400;
        text-align: center;
        vertical-align: middle;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
        padding: .375rem .75rem;
        font-size: 1.2rem;
        line-height: 1.5;
        border-radius: 0;
        color: #fff;
        background-color: #1177d1;
        border-color: #1177d1;
        transition: color .15s ease-in-out,background-color .15s ease-in-out,border-color .15s ease-in-out,box-shadow .15s ease-in-out;
    }

    .btn-apply:hover {
        background-color: #0d5ca2;
    }

    .apply-btn-container {
        padding-bottom: 14px;
    }

    .dropbtn {
        font-size: 14px;
        cursor: pointer;
        width: 90px;
        text-align: left;
    }

    /* The container <div> - needed to position the dropdown content */
    .dropdown {
        position: relative;
        display: inline-block;
    }

    /* Dropdown Content (Hidden by Default) */
    .dropdown-content {
        display: none;
        position: absolute;
        min-width: 120px;
        box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
        z-index: 1;
    }

    /* Links inside the dropdown */
    .dropdown-content a {
        color: black;
        padding: 3px 4px;
        text-decoration: none;
        display: block;
    }

    a:active .dropdown-content {
        display: none;
    }

    /* Change color of dropdown links on hover */
    .dropdown-content a:hover {background-color: #f1f1f1}

    /* Show the dropdown menu on hover */
    .dropdown:hover .dropdown-content {
        display: block;
    }

</style>
