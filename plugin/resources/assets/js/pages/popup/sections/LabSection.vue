<template>
    <div class="lab">
        <div class="section font pb-5" v-for="lab in labs">
            <h2 class="pl-5 font">{{getDayTimeFormat(lab.start.time)}}
                <button class="btn font" v-on:click="editLabClicked(lab)" style="background-color: #d7dde4">Edit</button></h2>
            <hr>
            <p class="pl-5">Date: {{getNiceDate(lab.start.time)}}</p>
            <p class="pl-5">Time: {{getNiceTime(lab.start.time)}} - {{getNiceTime(lab.end.time)}}</p>
            <p class="pl-5">Teachers: <b v-for="teacher in getTeachersInThisLab(lab.id)">{{teacher.firstName}} {{teacher.lastName}}, </b></p>
        </div>
        <button v-on:click="clickAddNewLabSession" class="font btn">+ Add a new lab session</button>
    </div>
</template>

<script>
    import {mapActions} from "vuex";

    export default {
        name: "LabSection.vue",
        props: {
            labs: {required: true}
        },

        methods: {
            ...mapActions(["updateLab", "updateLabToEmpty"]),

            clickAddNewLabSession() {
                this.updateLabToEmpty()
                window.location = "popup#/labsForm";
            },
            getNiceTime(time) {
                let mins = time.getMinutes().toString();
                if (mins.length == 1) {
                    mins = "0" + mins;
                }
                return time.getHours() + ":" + mins
            },
            getNiceDate(date) {
                let month = (date.getMonth() + 1).toString();
                if (month.length == 1) {
                    month = "0" + month
                }
                return date.getDate() + '.' + month + '.' + date.getFullYear()
            },
            getDayTimeFormat(date) {
                let daysDict = {0: 'P', 1: 'E', 2: 'T', 3: 'K', 4: 'N', 5: 'R', 6: 'L'};
                return daysDict[date.getDay()] + date.getHours();
            },
            getTeachersInThisLab(labId) {
                let teachers = [];
                axios.get('http://localhost:82/mod/charon/api/courses/1/labs/' + labId + '/teachers')
                    .then(response => (teachers = response));
                return teachers; // teachers.data
            },
            editLabClicked(lab) {
                this.updateLab({lab})
                window.location = "popup#/labsForm";
            }
        }
    }


</script>

<style scoped>

    button:hover {
        color: white;
    }
    hr {background-color: black; margin: 0}
    .font {font-size: 2vw; font-weight: 600;}
    .section {background-color: #d7dde4; border-style: solid; margin-bottom: 2vw;}
    .btn {float: right; border-style: none;}

</style>
