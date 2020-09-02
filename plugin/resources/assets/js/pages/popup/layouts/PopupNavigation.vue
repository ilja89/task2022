<template>


    <div class="ma-12 pa-12">
        <v-card>
            <v-navigation-drawer
                    permanent
                    expand-on-hover
            >
                <v-list
                        nav
                        dense
                >
                    <v-list-item link>
                        <v-list-item-icon>
                            <v-icon>mdi-folder</v-icon>
                        </v-list-item-icon>
                        <router-link :to="gradingLink">
                            Grading
                        </router-link>
                    </v-list-item>
                    <v-list-item link>
                        <v-list-item-icon>
                            <v-icon>mdi-account-multiple</v-icon>
                        </v-list-item-icon>
                        <v-list-item-title>Shared with me</v-list-item-title>
                    </v-list-item>
                    <v-list-item link>
                        <v-list-item-icon>
                            <v-icon>mdi-star</v-icon>
                        </v-list-item-icon>
                        <v-list-item-title>Starred</v-list-item-title>
                    </v-list-item>
                </v-list>
            </v-navigation-drawer>
        </v-card>
    </div>

</template>

<script>
    import {mapState, mapGetters} from 'vuex'

    export default {
        computed: {
            ...mapState([
                'student',
                'submission',
            ]),

            ...mapGetters([
                'submissionLink',
            ]),

            gradingLink() {
                if (this.student !== null) {
                    return '/grading/' + this.student.id
                } else {
                    return '/grading'
                }
            },

            studentOverviewLink() {
                if (this.student !== null) {
                    return '/student-overview/' + this.student.id
                } else {
                    return '/student-overview'
                }
            }
        }
    }
</script>
