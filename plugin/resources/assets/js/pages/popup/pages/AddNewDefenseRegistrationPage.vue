<template>
  <div>
      <v-card class="mb-16 pl-4">
        <v-card-title>New defense registration</v-card-title>
      </v-card>
    <popup-section
        title="Add new defense registration"
        subtitle="Here you can register student to defense"
    >
      <add-new-defense-registration-section></add-new-defense-registration-section>
    </popup-section>
  </div>
</template>

<script>

import AddNewDefenseRegistrationSection from "../sections/AddNewDefenseRegistrationSection";
import {PopupSection} from "../layouts";

export default {
  name: "add-new-defense-registration-page",
  components: {AddNewDefenseRegistrationSection, PopupSection}
}
</script>

<style>

</style>