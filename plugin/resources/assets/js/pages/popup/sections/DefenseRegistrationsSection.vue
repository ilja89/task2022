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
                    <td>{{ defense.choosen_time }}</td>
                    <td>{{ defense.student_name }}</td>
                    <td>{{ defense.defense_duration }} min</td>
                    <td>{{ defense.my_teacher }}</td>
                    <td>{{ defense.submission_id }}</td>
                    <td>Waiting</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script>
    import Datepicker from "../../../components/partials/Datepicker";
    export default {
        components: {Datepicker},
        data() {
            return {
                after: {time: null},
                before: {time: null}
            }
        }, props: {
            defenseList: {required: true}
        },
        mounted() {
            console.log(this.defenseList)
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
    }

    .datepick {
        padding-bottom: 14px;
    }

</style>
