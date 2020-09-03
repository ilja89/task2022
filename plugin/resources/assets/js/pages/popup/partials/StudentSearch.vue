<template>
    <v-row>

        <v-btn icon color="primary">
            <md-icon>search</md-icon>
        </v-btn>

        <div class="search-container">

            <autocomplete
                    :url="studentsSearchUrl"
                    anchor="fullname"
                    label=""
                    :on-select="onStudentSelected"
                    id="student-search"
                    placeholder="Student name (uniid@ttu.ee)"
                    :min="2"
            />

        </div>

        <v-btn icon color="primary" @click="clearClicked">
            <md-icon>clear</md-icon>
        </v-btn>

        <v-btn icon color="primary" @click="onRefreshClicked">
            <md-icon>refresh</md-icon>
        </v-btn>

        <extra-options/>

    </v-row>
</template>

<script>
    import autocomplete from 'vue2-autocomplete-js'
    import {mapGetters} from 'vuex'
    import ExtraOptions from "./ExtraOptions";

    export default {
        components: {autocomplete, ExtraOptions},

        computed: {
            ...mapGetters([
                'studentsSearchUrl',
            ]),
        },

        methods: {
            onRefreshClicked() {
                VueEvent.$emit("refresh-page");
            },

            clearClicked() {
                this.$children.forEach((child) => {
                    if (child.$options._componentTag === 'autocomplete') {
                        child.setValue('')
                    }
                })
                document.getElementById('student-search').focus()
            },

            onStudentSelected(student) {
                this.$emit('student-was-changed', student)
            },
        },
    }
</script>
