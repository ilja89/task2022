<template>
    <div class="columns is-gapless  popup-body">

        <aside class="column is-2  menu  nav-container">
            <ul class="menu-list">

                <li v-for="page in pages" class="nav-item">
                    <a @click="onPageClicked(page)" :class="{ 'is-active': page.isActive }">
                        {{ page.name }}
                    </a>
                </li>

            </ul>
        </aside>

        <div class="column  page-container">
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
            VueEvent.$on('change-page', pageName => {
                this.selectPage({ name: pageName });
            });
        },

        methods: {
            selectPage(selectedPage) {
                this.pages.forEach(page => {
                    page.isActive = (page.name === selectedPage.name);
                });
            },

            onPageClicked(page) {
                VueEvent.$emit('change-page', page.name);
            }
        }
    }
</script>
