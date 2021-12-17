<template>
    <v-dialog persistent content-class="dialogBox" v-model="alert" transition="dialog-bottom-transition">

        <div>
            <p content-class="question" v-text="question"></p>
        </div>

        <div>
            <p content-class="text" v-text="text"></p>
        </div>

        <v-row>
            <v-col v-for="name in buttonNames" :key="name">
                <v-btn content-class="customBtn" v-text="name" @click="closeDialog(name)"></v-btn>
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
}

.dialogBox div p {
    margin: auto;
    font-size: larger;
}

.text {
    margin: auto;
    alignment: center;
    font-size: medium;
}

.dialogBox v-row v-col v-btn {
    color: #2995e8;
}

</style>
