<template>
    <div>
        <page-title title="Labs"></page-title>
        <LabSection v-bind:LabsList="LabsList"/>
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
    }
</script>