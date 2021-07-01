<template>
  <popup-section title="General information"
                 subtitle="Here's some general information about the activity.">
    <div>
      <p>Charon name: </p>
      <p>Max points: </p>
      <p>Deadline: </p>
    </div>
  </popup-section>
</template>

<script>
import {PopupSection} from "../layouts";

export default {
  name: "GeneralInformationSection",

  components: {PopupSection},

  created() {
    console.log(this.$store.state.charon)
  }
}
</script>

<style scoped>

</style>