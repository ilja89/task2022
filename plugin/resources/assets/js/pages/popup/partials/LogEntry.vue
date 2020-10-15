<template>

    <v-list-group
      v-if="Array.isArray(children)"
      :key="title"
      :prepend-icon=icon
      no-action
    >
        <template v-slot:activator>
            <v-list-item-content>
                <v-list-item-title v-text="title"></v-list-item-title>
            </v-list-item-content>
        </template>

        <v-list-item v-for="child in children" :key="child">
            <v-list-item-content>
                <v-list-item-title v-text="child"></v-list-item-title>
            </v-list-item-content>
        </v-list-item>
    </v-list-group>

    <v-list-item v-else :key="title" :prepend-icon=icon>
        <v-list-item-title v-text="title"></v-list-item-title>
    </v-list-item>

</template>

<script>
export default {
    name: 'LogEntry',
    props: {
        log: Array
    },
    computed: {
        title: function () {
            return this.log[0];
        },
        children: function () {
            if (this.log.length === 1) {
                return false;
            }
            return this.log.slice(1);
        },
        icon: function () {
            let level = this.log[0].split(':')[2].split('.')[1];
            if (level === 'WARNING') {
                return 'mdi-alert-outline';
            }

            if (level === 'ERROR' || level === 'CRITICAL') {
                return 'mdi-alert-octagram';
            }

            return 'mdi-alert-circle-outline';
        }
    }
}
</script>
