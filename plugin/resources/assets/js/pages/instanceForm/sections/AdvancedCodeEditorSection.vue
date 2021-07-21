<template>

  <div class="fcontainer clearfix fitem">

    <p>Advanced Code Editor</p>
    <button v-on:click="this.getTemplates">
      getTemp
    </button>
  </div>

</template>

<script>
import {Charon} from "../../../api";

export default {
  name: "AdvancedCodeEditorSection",

  props: {
    form: {required: true}
  },
  methods: {
    getTemplates() {
      console.log(this.form.fields.preset.id);
      try {
        Charon.getTemplates(window.charonId, answer => {
          console.log(answer)
        })
      } catch (e) {
        VueEvent.$emit('show-notification', 'Error getting templates!')
      }
    },

  }
}

</script>

<style scoped>

</style>