<template>
    <div class="columns is-gapless popup-body">

        <div class="column" id="page-right">
            <div class="page-container">
                <keep-alive>
                    <router-view/>
                </keep-alive>
            </div>
        </div>

    </div>
</template>

<script>

export default {
  mounted() {
    VueEvent.$on('change-popup-submission-url', (submissionId) => {
      this.$router.push(`/submissions/${submissionId}`);
    });
  },
}

</script>
