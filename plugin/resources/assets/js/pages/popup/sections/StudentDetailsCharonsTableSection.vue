<template>
    <popup-section title="Charons' details"
                   subtitle="Here's some info on charons for the selected student.">
        <v-card-title v-if="table.length">
            Charons
            <v-spacer></v-spacer>
            <v-text-field
                v-if="table.length"
                v-model="search"
                append-icon="search"
                label="Search"
                single-line
                hide-details>
            </v-text-field>
        </v-card-title>
        <v-card-title v-else>
            No Charons for this student!
        </v-card-title>

        <v-data-table
            v-if="table.length"
            :headers="table_headers"
            :items="table"
            :search="search">
            <template v-slot:no-results>
                <v-alert :value="true" color="primary" icon="warning">
                    Your search for "{{ search }}" found no results.
                </v-alert>
            </template>
            <template v-slot:item.defended="{ item }">
                <v-chip :color="getDefColor(item.defended)" dark> {{ item.defended | defFilter }}</v-chip>
            </template>
            <template v-slot:item.points="{ item }">
                <v-chip :color="getPtsColor(item.maxPoints, item.studentPoints, item.defThreshold)" dark>
                    {{ item.points | pointsFilter(item.maxPoints, item.studentPoints) }}
                </v-chip>
            </template>
        </v-data-table>

    </popup-section>

</template>

<script>
import {PopupSection} from '../layouts/index'
import Charon from "../../../api/Charon";

export default {
    name: "StudentDetailsCharonsTableSection",

    data() {
        return {
            alert: false,
            search: '',
            table_headers: [
                {text: 'Charon', value: 'charonName', align: 'start'},
                {text: 'Points', value: 'points'},
                {text: 'Defended', value: 'defended'}
            ]
        }
    },

    props: ['table'],

    components: {PopupSection},

    methods: {
        getDefColor(defended) {
            if (defended === 1) return 'green'
            else return 'transparent'
        },

        getPtsColor(maxPoints, studentPoints, threshold) {
            if (parseFloat(studentPoints) >= (parseFloat(maxPoints) * threshold) / 100.0) return 'green'
            else return 'red'
        }
    },

    filters: {
        defFilter: function (value) {
            return (value === 1) ? 'Yes' : '';
        },

        pointsFilter: function (value, maxPoints, studentPoints) {
            studentPoints = studentPoints ? studentPoints : "0.0";
            return parseFloat(studentPoints).toFixed(2) + ' p / ' + parseInt(maxPoints) + ' p';
        }
    }
}
</script>

<style scoped>

</style>