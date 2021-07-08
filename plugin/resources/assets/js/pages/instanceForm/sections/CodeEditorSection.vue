<template>

  <fieldset class="clearfix collapsible" id="id_modstandardelshdr">

<!--    <legend class="ftoggler">{{ translate('grouping') }}</legend>-->
    <legend class="ftoggler">Code Editor</legend>

    <div class="fcontainer clearfix fitem">

      <label> Add code editor to this charon:
        <input type="checkbox" name="editor_set" v-model="form.fields.editor_set" value="true">
      </label>

    </div>

  </fieldset>

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