<template>
  <popup-section title="General information"
                 subtitle="Here's some general information about the activity.">
      <v-card class="ges-card">
        <v-card-text>Charon name: {{ charon.name }}</v-card-text>
        <v-card-text>Max points: {{  maxPoints }}</v-card-text>
        <v-card-text>Deadline: {{ charon.defense_deadline }}</v-card-text>
      </v-card>
  </popup-section>
</template>

<script>
import {PopupSection} from "../layouts";

export default {
  name: "GeneralInformationSection",

  components: {PopupSection},

  props: ['charon'],

  computed: {
    maxPoints() {
      let thisCharon = {};
      if (this.$store.state.charons) {
        const charons = this.$store.state.charons;
        for (let charonIndex in charons) {
          if (charons[charonIndex].id === parseInt(this.$route.params.charon_id)) {
            thisCharon = charons[charonIndex];
          }
        }
        if (thisCharon['grademaps']) {
          let maxPoints = thisCharon['grademaps']['0']['grade_item']['grademax'];
          return parseFloat(maxPoints);
        }
      }
      return ''
    }
  }
}
</script>

<style scoped>
.ges-card {
  display: flex;
  flex-direction: row;
  justify-content: space-evenly;
}

</style>