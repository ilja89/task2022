<template>
<div>
  <div class="fcontainer clearfix fitem">
    <p>Advanced Code Editor</p>
    <button v-on:click="this.getTemplates">
      getTemp
    </button>
  </div>
  <div>
    <input @input-was-changed="sendTemplates" type="hidden" :name="'templates'" v-model="templates">
  </div>
</div>
</template>

<script>
import {Charon} from "../../../api";
import {EmitEventOnInputChange} from '../../../mixins';
export default {
  name: "AdvancedCodeEditorSection",

  mixins: [ EmitEventOnInputChange ],

  props: {
    form: {required: true},
    },

  data() {
    return {
      templates: [{path:'EX01/Car.java', contents:'import java.util.Set;'}],
    }
  },

  mounted() {
    console.log([{path:'EX01/Car.java', contents:'import java.util.Set;'}]);
    this.form.fields.templates = [{path:'EX01/Car.java', contents:'import java.util.Set;'}]; //TODO not working, why? I dont know
    console.log(this.form.fields.templates);
  },

  methods: {

    getTemplates() {
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