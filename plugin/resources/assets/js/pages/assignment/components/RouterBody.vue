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
    VueEvent.$on('change-open-submission-url', (submissionId) => {
      this.$router.push(`/submissionPage/${submissionId}`);
    });
  },
}

</script>
