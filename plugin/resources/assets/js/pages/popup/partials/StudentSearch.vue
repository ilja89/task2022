<template>
    <div class="search-container">

        <md-icon @click="clearClicked">search</md-icon>

        <autocomplete
            :url="studentsSearchUrl"
            anchor="fullname"
            label=""
            :on-select="onStudentSelected"
            id="student-search"
            placeholder="Student name (uniid@ttu.ee)"
            :min="2"
        />

        <md-icon @click="clearClicked">clear</md-icon>

    </div>
</template>

<script>
    import autocomplete from 'vue2-autocomplete-js'
    import { mapGetters } from 'vuex'

    export default {
        components: { autocomplete },

        computed: {
            ...mapGetters([
                'studentsSearchUrl',
            ]),
        },

        methods: {
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
