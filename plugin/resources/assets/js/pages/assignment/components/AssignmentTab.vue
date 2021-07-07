<template>
  <charon-tabs>
    <charon-tab v-for="(code, index) in codes"
                :name="code"
                :selected="index===0">
      <code-editor :code="code"></code-editor>
    </charon-tab>
  </charon-tabs>
</template>
<script>
import CharonTab from "../../../components/partials/CharonTab";
import CharonTabs from "../../../components/partials/CharonTabs";
import CodeEditor from "./CodeEditor";
export default {
  name: "AssignmentTab",

  components: {CharonTab, CharonTabs, CodeEditor},

  props: {

  },

  data() {
    return {
      codes: ["def", "def2", "def3"]
    }
  }
}
</script>