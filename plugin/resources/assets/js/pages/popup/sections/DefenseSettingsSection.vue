<template>

    <div class="lab">
        <div class="section font pb-5" v-for="charon in charons">
            <h2 class="pl-5 font">{{charon.name}}
                <button class="btn font" v-on:click="editClicked(charon)">Edit</button></h2>
            <hr>
            <p class="pl-5">Deadline: {{getDateFormatted(charon.defense_deadline.time)}}</p>
            <p class="pl-5">Duration: {{getDurationFormatted(charon.defense_duration)}}</p>
            <p class="pl-5">Labs: <b v-for="lab in charon.charonDefenseLabs">{{lab.name}}<b v-if="lab !== charon.charonDefenseLabs[charon.charonDefenseLabs.length - 1]">, </b></b> </p>
        </div>
    </div>
</template>

<script>
    import {mapActions} from "vuex";

    export default {

        props: {
            charons: {required: true}
        },
        methods: {
            ...mapActions(["updateCharon"]),

            editClicked(charon) {
                this.updateCharon({charon});
                window.location = 'popup#/defSettingsEditing'
            },
            getDurationFormatted(duration) {
                if (duration !== null) {
                    return duration + ' min'
                }
            },
            getDateFormatted(date) {
                if (date === null) {
                    return date
                }
                return date.getDate() + '.' + ('0' + (date.getMonth() + 1)).substr(-2, 2) + '.' + date.getFullYear() +
                    ' ' + ('0' + date.getHours()).substr(-2, 2) + ':' + ('0' + date.getMinutes()).substr(-2, 2)
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
            getNameForLab(labStart) {
                return this.getDayTimeFormat(new Date(labStart))
                    + ' (' + this.getDateFormatted(new Date(labStart)) + ')'
            }
        },
    }
</script>

<style scoped>

    button:hover {color: white;}
    hr {background-color: black; margin: 0}
    .font {font-size: 2vw; font-weight: 600;}
    .section {background-color: #d7dde4; border-style: solid; margin-bottom: 2vw;}
    .btn {float: right; border-style: none; background-color: #d7dde4;}

</style>