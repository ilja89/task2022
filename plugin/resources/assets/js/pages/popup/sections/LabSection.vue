<template>
    <div class="lab">
        <div class="section font pb-5" v-for="lab in LabsList">
            <h2 class="pl-5 font">{{lab.id}} <button class="btn font" style="background-color: #d7dde4">Edit</button></h2>
            <hr>
            <p class="pl-5">Date: {{lab.date}}</p>
            <p class="pl-5">Time: {{lab.time}}</p>
            <p class="pl-5">Teachers: <b v-for="teacher in lab.teachers">{{teacher}}, </b> ...</p>
        </div>
        <button class="font btn">+ Add a new lab session</button>
    </div>
</template>

<script>
    export default {
        name: "LabSection.vue",
        props: ["LabsList"],
    }


</script>

<style scoped>

    button:hover {
        color: white;
    }
    hr {background-color: black; margin: 0}
    .font {font-size: 2vw; font-weight: 600;}
    .section {background-color: #d7dde4; border-style: solid; margin-bottom: 2vw;}
    .btn {float: right; border-style: none;}

</style>