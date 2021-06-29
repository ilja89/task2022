<template>

  <v-card class="mx-auto mb-16">
    <v-system-bar color="blue"></v-system-bar>
    <v-toolbar flat>
      <v-toolbar-title v-html="title"></v-toolbar-title>
      <v-spacer></v-spacer>
      <slot name="header-right"></slot>
    </v-toolbar>
    <v-banner class="pa-3" sticky v-html="subtitle">
    </v-banner>
    <v-card-text class="grey lighten-4">
      <v-container class="spacing-playground pa-3" fluid>
        <slot></slot>
      </v-container>
    </v-card-text>
  </v-card>
</template>

<script>
export default {
  props: {
    name: "popup-section",

    title: {required: true},
    subtitle: {
      required: false,
      default: ''
    }
  }
}
</script>

