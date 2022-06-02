<template>
  <div>
    <v-card class="mb-16 pl-4">
      <v-card-title>Charon Chain v6</v-card-title>
      <v-card-text>{{testFunc}}</v-card-text>
    </v-card>
  </div>
</template>

<script>
import CharonChain from "../../../api/CharonChain";

export default {
  name: "charon-chain-page",

  computed: {
    testFunc() {
      this.res = -1;
      CharonChain.testFunc(3, (result) => {this.res = result});
      return this.res;
    },
  }
}





</script>

<style scoped>

</style>