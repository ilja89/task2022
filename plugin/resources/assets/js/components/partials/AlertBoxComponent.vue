<template>
    <v-dialog persistent content-class="dialogBox" v-model="alert" transition="dialog-bottom-transition">

        <div>
            <p class="question" v-text="question"></p>
        </div>

        <div>
            <p class="charon-info" v-text="text"></p>
        </div>

        <v-row class="alertBox-buttons">
            <v-col v-for="name in buttonNames" :key="name">
                <v-btn tile class="customBtn" v-text="name" @click="closeDialog(name)"></v-btn>
            </v-col>
        </v-row>
    </v-dialog>
</template>

<script>

export default {
    name: 'AlertBoxComponent',

    props: {
        eventName: {required: true},
        question: {required: false },
        text: { required: false },
        buttonNames: { required: false },
    },

    data() {
        return {
            alert: true,
        }
    },

    methods: {
        closeDialog(buttonName) {
            VueEvent.$emit(this.eventName, buttonName);
        }
    },
}

</script>

<style>

.dialogBox {
    margin: auto;
    position: relative;
    z-index: 2000;
    background-color: whitesmoke;
    alignment: center;
}

.dialogBox div p {
    margin: auto;
    font-size: larger;
}

v-text {
    margin: auto;
    alignment: center;
    font-size: medium;
    color: #1976d2;
}

.question {
    font-weight: bold;
    text-align: center;
    inline-size:30em;
}

.dialogBox v-row v-col v-btn {
    color: #2995e8;
}

.customBtn {
    border: #1976d2;
    margin: 2em .3em 0 .3em;
    color: #1976d2!important;
    border: thin solid;
    box-shadow: unset!important;
    text-transform: uppercase;
}

div.v-dialog {
    width: fit-content;
    overflow-y: unset;
    margin: unset;
    padding: 1em;
}

.charon-info {
    text-align: center;
}

div.row.alertBox-buttons .col{
    flex-grow: unset;
}

div.row.alertBox-buttons {
    justify-content: center;
    align-items: center;
}

v-btn {
    box-shadow: unset;
}

</style>
