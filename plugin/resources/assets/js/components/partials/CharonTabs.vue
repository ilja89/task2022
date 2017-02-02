<template>
    <div class="fitem">
        <div class="tabs">
            <ul class="tabs-list">
                <li v-for="tab in tabs" class="tab" :class="{ 'is-active': tab.isActive }">
                    <a @click="selectTab(tab)">{{ tab.name }}</a>
                </li>
            </ul>

            <div class="tabs-right  is-pulled-right">
                <slot name="tabs-right"></slot>
            </div>
        </div>

        <div class="tab-details">
            <slot></slot>
        </div>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                tabs: []
            }
        },

        mounted() {
            this.tabs = this.$children;
        },

        methods: {
            selectTab(selectedTab) {
                this.tabs.forEach(tab => {
                    tab.isActive = (tab.name === selectedTab.name);
                });
            }
        }
    }
</script>
