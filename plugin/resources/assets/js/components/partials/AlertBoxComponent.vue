<template>
    <v-dialog persistent content-class="dialogBox" v-model="alert" transition="dialog-bottom-transition">

        <div>
            <p class="question" v-text="question"></p>
        </div>

        <div>
            <p class="text" v-text="text"></p>
        </div>

        <v-row>
            <v-col v-for="name in buttonNames" :key="name">
                <v-btn class="customBtn" v-text="name" @click="closeDialog(name)"></v-btn>
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
}

.dialogBox div {
    alignment: center;
    width: fit-content;
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
}

.dialogBox v-row v-col v-btn {
    color: #2995e8;
}

.customBtn {
    border: #1976d2;
    margin: 2em 1em 0 1em;
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

v-btn {
    box-shadow: unset;
}

</style>
