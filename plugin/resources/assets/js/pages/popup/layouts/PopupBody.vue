<template>

    <v-row>

        <v-col>
            <popup-navigation/>
        </v-col>

        <v-col>
            <div class="page-container">
                <keep-alive>
                    <router-view/>
                </keep-alive>
            </div>
        </v-col>

    </v-row>
</template>

<script>
    import PopupNavigation from './PopupNavigation.vue';

    export default {
        components: {PopupNavigation},
    }
</script>
