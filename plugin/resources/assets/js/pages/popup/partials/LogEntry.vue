<template>

    <v-list-group
            v-if="Array.isArray(children)"
            :key="title"
            no-action
    >
        <template v-slot:activator>
            <v-list-item-content>
                <v-alert class="multi-line" dense :outlined="outline" :type="level">{{ title }}</v-alert>
            </v-list-item-content>
        </template>

        <v-list-item v-for="(child, index) in children" :key="index">
            <v-list-item-content>
                <v-list-item-title v-text="child"></v-list-item-title>
            </v-list-item-content>
        </v-list-item>
    </v-list-group>

    <v-list-item v-else :key="title">
        <v-list-item-title>
            <v-alert class="multi-line" dense :outlined="outline" :type="level">{{ title }}</v-alert>
        </v-list-item-title>
    </v-list-item>

</template>

<script>
    const pattern = /^\[\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}:\d{2}\]\s\w+\.(\w+):\s/;
    const extractLevel = function (title) {
        let level = title.match(pattern);
        if (level === undefined || level === null) {
            return 'info';
        }
        return level[1].toLowerCase();
    }

    export default {
        props: {
            name: 'log-entry',
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
            level: function () {
                let level = extractLevel(this.title);
                switch (level) {
                    case 'error':
                    case 'success':
                    case 'warning':
                        return level;
                    case 'critical':
                        return 'warning';
                    default:
                        return 'info';
                }
            },
            outline: function () {
                return this.level === 'info';
            }
        }
    }
</script>

<style lang="scss" scoped>
    .multi-line {
        white-space: pre-line;
        display: inline-block;
        word-break: break-word;
    }

    .v-list-item {
        line-height: 1.3;
    }

    .v-list-item .v-list-item__subtitle {
        line-height: 2.0;
    }

    .v-list-item__subtitle, .v-list-item__title {
        white-space: pre-wrap;
    }

    .v-list-item__content {
        padding: 0;
    }

    .v-list-group__items .v-list-item {
        min-height: auto;
    }

    .v-alert {
        margin: 8px 0;
    }

    .v-list-item__content {
        overflow-wrap: anywhere;
    }
</style>
