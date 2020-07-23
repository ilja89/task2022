<template>
    <div>
        <page-title title="Labs"></page-title>
        <LabSection v-bind:labs="labs"/>
    </div>
</template>

<script>
    import { PageTitle } from '../partials'
    import {
        LabSection,
    } from '../sections'
    import {mapState} from "vuex";

    export default {
        name: "labs-page",
        data() {
            return {
                labs: {},
                LabsList: [
                    {id:"T14", date: "02.09.2020", time: "14:00 - 15:30", teachers: ["Kadri Männimets", "Nikita Ojamäe", "Ago Luberg"]},
                    {id:"N9", date: "04.09.2020", time: "9:00 - 10:30", teachers: ["Kadri Männimets", "Nikita Ojamäe", "Ago Luberg"]},
                ]
            }
        },

        components: {
            PageTitle, LabSection
        },
        mounted() {

            axios.get('http://localhost:82/mod/charon/api/courses/' + this.course.id + '/labs')
                .then(response => {
                    this.labs = response.data;
                    this.formatLabs()});
            //console.log(this.given);
        },
        computed: {

            ...mapState([
                'lab',
                'course'
            ]),
        },
        methods: {
            formatLabs() {
                for (let i = 0; i < this.labs.length; i++) {
                    let save_start = this.labs[i].start
                    this.labs[i].start = {time: new Date(save_start)}
                    let save_end = this.labs[i].end
                    this.labs[i].end = {time: new Date(save_end)}
                }
            }

        }
    }
</script>
