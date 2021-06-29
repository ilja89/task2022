<template>
  <div>

    <page-title :title="page_name"></page-title>

  </div>
</template>

<script>
import {mapState} from "vuex";
import {PageTitle} from '../../popup/partials'

export default {
  name: "dashboard-page",

  computed: {
    ...mapState([
      'is_mobile'
    ]),

    page_name() {
      return `Dashboard - ${window.course_name}`
    },
  },

  components: {
    PageTitle
  },
}
</script>
