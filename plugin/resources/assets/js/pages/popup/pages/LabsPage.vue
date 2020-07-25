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
                labs: {}
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
