<template>
  <v-card class="mx-auto mb-4">
    <v-card-text class="grey lighten-4">
      <v-container class="spacing-playground pa-3" fluid>
        <v-card-title>
          {{ translate('queueText') }}
        </v-card-title>

        <v-layout column style="height: 125vh">
          <v-flex md6 style="overflow: auto">
            <v-data-table
                :headers="headers"
                :items="items.registrations"
            >
            </v-data-table>
          </v-flex>
        </v-layout>
      </v-container>
    </v-card-text>
  </v-card>
</template>

<script>
import {Translate} from "../../../mixins";

export default {
  name: "registration-queue",

  props: ['items'],

  mixins: [Translate],

  data() {
    return {
      headers: [
        {text: this.translate("nrInQueueText"), value: 'queue_pos', align: 'start', sortable: false},
        {text: this.translate("charonText"), value: 'charon_name', sortable: false},
        {text: this.translate("estimatedStartTimeText"), value: 'approx_start_time', sortable: false},
        {text: this.translate("studentText"), value: 'student_name', sortable: false},
      ],
    }
  },
}
</script>

<style>

</style>
