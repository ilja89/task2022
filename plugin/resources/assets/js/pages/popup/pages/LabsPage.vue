<template>
    <div>
        <page-title title="Labs"></page-title>
        <LabSection v-bind:labs="given.data"/>
        <a v-on:click="ok">OK</a>
    </div>
</template>

<script>
    import { PageTitle } from '../partials'
    import {
        LabSection,
    } from '../sections'

    export default {
        name: "labs-page",
        data() {
            return {
                given: {},
                LabsList: [
                    {id:"T14", date: "02.09.2020", time: "14:00 - 15:30", teachers: ["Kadri Männimets", "Nikita Ojamäe", "Ago Luberg"]},
                    {id:"N9", date: "04.09.2020", time: "9:00 - 10:30", teachers: ["Kadri Männimets", "Nikita Ojamäe", "Ago Luberg"]},
                ]
            }
        }
        ,
        components: {
            PageTitle, LabSection
        },
        mounted() {
            //console.log(this.given);
            axios.get('http://localhost:82/mod/charon/api/courses/1/labs')
                .then(response => (this.given = response));
            //console.log(this.given);
        },
        methods: {
            ok() {
                console.log("Ok clicked")
                console.log(this.given)
            }
        }
    }
</script>