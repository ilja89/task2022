<template>
  <div class="student-overview-container">
    <page-title :title="page_name"></page-title>
  </div>

</template>

<script>
import {PageTitle} from '../partials'
import {mapState} from 'vuex'
import Charon from "../../../api/Charon";

export default {
  name: "ActivityDashboardPage",

  components: {PageTitle},

  data() {
    return {
    }
  },

  computed: {
    ...mapState([
      "course",
      "charon"
    ]),
    version: function () { return window.appVersion; },

    routeCharonId() {
      return this.$route.params.charon_id
    },

    page_name() {
      if (this.charon) {
        return 'Charon dashboard: ' + this.charon.name
      }
      return 'Charon dashboard'
    }
  },

  created() {
    document.title = this.page_name;

    Charon.getById(this.routeCharonId, response => {
      this.$store.state.charon = response
      this.charon = response
    })
  },

  methods: {
  },
}
</script>

<style scoped>

</style>