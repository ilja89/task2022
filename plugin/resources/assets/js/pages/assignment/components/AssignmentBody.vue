<template>
  <div>
    <keep-alive>
      <router-view :key="$route.fullPath"></router-view>
    </keep-alive>
  </div>
</template>

<script>

export default {
  mounted() {
    VueEvent.$on('change-assignment-submission-url', (submissionId) => {
      this.$router.push(`/submission-page/${submissionId}`);
    });
  },
}

</script>
