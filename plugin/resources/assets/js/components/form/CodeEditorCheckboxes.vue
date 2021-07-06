<template>
  <div class="fcontainer clearfix">
    <div class="fitem fitem_fcheck">
      <div class="fitemtitle">
        <label>{{ label }}</label>
      </div>
      <div class="felement fcheck grades-select-container">
        <div class="grades-select-col">
          <label class="checkbox" v-for="grade_type in getAll">
            <input type="checkbox" @click="toggleClicked(grade_type.code)"
                   :checked="isActive(grade_type.code)">
            {{ grade_type.name }}
          </label>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "CodeEditorCheckboxes",
  methods: {
    toggleClicked(code) {
      if (this.isActive(code)) {
        const index = this.active.indexOf(code);
        if (index !== -1) {
          this.active.splice(index, 1);
        }

        this.$emit('grade-type-was-deactivated', code);
      } else {
        this.active.push(code);

        this.$emit('grade-type-was-activated', code);
      }
    },
    getAll() {
      return [];
    }
  },

  isActive(code) {
    return this.active.includes(code);
  },
}
</script>

<style scoped>

</style>