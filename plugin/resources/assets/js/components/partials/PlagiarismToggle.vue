<template>
    <v-btn-toggle rounded borderless dense v-model="activeStatus">
        <v-btn v-if="originalStatus !== 'acceptable'" @click="updateStatus('acceptable')" icon active-class="accepted-button">
            <v-icon aria-label="Accepted" role="button" aria-hidden="false">mdi-thumb-up-outline</v-icon>
        </v-btn>
        <v-btn v-if="originalStatus !== 'plagiarism'" @click="updateStatus('plagiarism')" icon active-class="plagiarism-button">
            <v-icon aria-label="Plagiarism" role="button" aria-hidden="false">mdi-thumb-down-outline</v-icon>
        </v-btn>
    </v-btn-toggle>
</template>

<script>

export default {

    name: 'PlagiarismToggle',

    props: {
        chosenStatus: { required: true },
        originalStatus: { required: true },
        matchId: { required: true }
    },

    data() {
        return {}
    },

    computed: {
        activeStatus() {
            return this.chosenStatus
        }
    },

    methods:{
        updateStatus(newStatus) {
            VueEvent.$emit('change-editor-content', this.matchId, newStatus);
        }
    }
}
</script>