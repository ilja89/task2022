<template>
    <v-card class="message">
        {{ translate('noCommentsInfo') }}
    </v-card>
</template>

<script>

import {Translate} from "../../mixins";

export default {
    name: "NoCommentsComponent",

    mixins: [Translate],
}
</script>

<style scoped>

.message {
    padding: 10px;
}

</style>