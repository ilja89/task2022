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
                    <td>{{ defense.time }}</td>
                    <td>{{ defense.student }}</td>
                    <td>{{ defense.duration }}</td>
                    <td>{{ defense.teacher }}</td>
                    <td>{{ defense.submission }}</td>
                    <td>{{ defense.progress }}</td>
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
                defenseList: [{time: '05.08.2020 14:00', student: 'Kirill Kotkas', duration: '10 min', teacher: 'any', submission: 'linktosubmission', progress: 'done'},
                    {time: '05.08.2020 14:10', student: 'Toomas Kana', duration: '10 min', teacher: 'any', submission: 'linktosubmission', progress: 'defending'},
                    {time: '05.08.2020 14:20', student: 'Priit Tuhk', duration: '10 min', teacher: 'Nikita Ojamäe', submission: 'linktosubmission', progress: 'waiting'}],
                after: {time: '04.08.2020 12:00'},
                before: {time: '09.08.2020 12:00'}
            }
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
