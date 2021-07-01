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
      if (this.$store.state.charon) {
        return 'Charon dashboard: ' + this.$store.state.charon.name
      }
      return 'Charon dashboard'
    },
  },

  watch: {
    $route() {
      if (typeof this.routeCharonId !== 'undefined' && this.$route.name === 'activity-dashboard') {
        this.getCharon()
      }
    },
  },

  created() {
    this.getCharon()
  },

  methods: {
    getCharon() {
      Charon.getById(this.routeCharonId, response => {
        this.$store.state.charon = response
      })
      document.title = this.page_name
    }
  },
}
</script>

<style scoped>

</style>