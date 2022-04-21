<template>
    <v-row justify="center">
        <v-dialog
            v-model="dialog"
            persistent
            max-width="800px"
        >
            <template v-if="this.newStatus === 'acceptable'" v-slot:activator="{ on, attrs }">
                <v-btn
                    class="accepted-button"
                    @click="showUpdateStatusModal()"
                    v-bind="attrs"
                    v-on="on"
                    icon>
                    <v-icon aria-label="Accepted" role="button" aria-hidden="false">mdi-thumb-up-outline</v-icon>
                </v-btn>
            </template>

            <template v-else v-slot:activator="{ on, attrs }">
                <v-btn
                    class="plagiarism-button"
                    @click="showUpdateStatusModal()"
                    v-bind="attrs"
                    v-on="on"
                    icon>
                    <v-icon aria-label="Plagiarism" role="button" aria-hidden="false">mdi-thumb-down-outline</v-icon>
                </v-btn>
            </template>

            <v-card>
                <v-card-title>
                    <span class="text-h5">Update match status</span>
                </v-card-title>

                <v-textarea
                    outlined
                    auto-grow
                    filled
                    :rules="[rules.length(2)]"
                    label="Comment"
                    v-model="comment"
                    class="pa-4"
                ></v-textarea>

                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn
                        color="blue darken-1"
                        text
                        @click="close"
                    >
                        Close
                    </v-btn>
                    <v-btn
                        color="blue darken-1"
                        text
                        @click="updateStatus"
                    >
                        Save
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-row>
</template>

<script>
export default {
    name: "PlagiarismUpdateStatusModal",

    props: {
        match: {
            required: true
        },
        newStatus: {
            required: true
        }
    },

    data() {
        return {
            dialog: false,
            comment: 'reason for the change',
            rules: {
                length: len => v => (v || '').length >= len || `Invalid character length, required ${len}`
            }
        }
    },

    methods: {
        showUpdateStatusModal() {
            this.dialog = true
        },

        updateStatus() {
            this.dialog = false
            this.$emit('updateStatus', this.match, this.newStatus, this.comment)
        },
        close() {
            this.dialog = false
            this.$emit('closeModal')
        }
    }
}
</script>

<style scoped>
.plagiarism-button {
    background-color: #f44336 !important;
}

.accepted-button {
    background-color: #56a576 !important;
}
</style>