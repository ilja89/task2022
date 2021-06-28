<template>

  <div id="container"></div>

</template>

<script>
export default {

  name: "code-editor",

  created() {
    monaco.editor.create(document.getElementById("container"), {
      value: "function hello() {\n\talert('Hello world!');\n}",
      language: "javascript"
    });
  }

}
</script>
