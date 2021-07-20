<template>

  <div class="fcontainer clearfix fitem">

    <label>
      <input id="setEditor" type="checkbox" name="editor_set" v-model="form.fields.editor_set" value="true">
      Add code editor to this charon
    </label>

  </div>

</template>

<script>

export default {

  name: "CodeEditorSection",

  props: {
    form: {required: true}
  }

}

</script>

<style>

</style>