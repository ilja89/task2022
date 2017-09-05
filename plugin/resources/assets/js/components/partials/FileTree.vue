<template>
  <ul class="file-tree">
    <file-tree-row
      v-for="(dataRow, index) in data"
      :key="index"
      :data="dataRow"
      @file-clicked="$emit('file-clicked', $event)">
    </file-tree-row>
  </ul>
</template>

<script>
  import FileTreeRow from './FileTreeRow'

  export default {
    name: 'FileTree',

    components: { FileTreeRow },

    props: {
      data: { required: true },
    },
  }
</script>

<style lang="scss">

  .file-tree {
    padding-left: 0;
    margin-top: 0;
    margin-bottom: 0;
    display: inline-block;
    min-width: 100%;
    min-height: 100%;
    background-color: #4D5158;

    > .file-tree-row > .file-tree-row__title-row {
      background-color: #4D5158;
      border-bottom-color: #52565d;

      &:hover {
        background-color: #52565d;
      }
    }
  }

</style>
