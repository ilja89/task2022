<template>
    <div class="columns is-gapless">

        <aside class="column is-2  menu  nav-container">
            <ul class="menu-list">

                <li v-for="page in pages" class="nav-item">
                    <a @click="selectPage(page)" :class="{ 'is-active': page.isActive }">
                        {{ page.name }}
                    </a>
                </li>

            </ul>
        </aside>

        <div class="column">
            <slot></slot>
        </div>

    </div>

</template>

<script>
    export default {
        data() {
            return {
                pages: []
            };
        },

        mounted() {
            this.pages = this.$children;
        },

        methods: {
            selectPage(selectedPage) {
                this.pages.forEach(page => {
                    page.isActive = (page.name === selectedPage.name);
                });
            }
        }
    }
</script>
