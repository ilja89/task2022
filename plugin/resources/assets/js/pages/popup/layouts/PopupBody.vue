<template>

    <v-row>

        <popup-navigation/>

        <div class="page-container">
            <keep-alive>
                <router-view/>
            </keep-alive>
        </div>

    </v-row>
</template>

<script>
    import PopupNavigation from './PopupNavigation.vue';

    export default {
        components: {PopupNavigation},
    }
</script>
