<template>
    <div class="columns is-gapless popup-body">

        <popup-navigation></popup-navigation>

        <div class="column page-right" id="page-right">
            <div class="page-container">
                <keep-alive>
                    <router-view></router-view>
                </keep-alive>
            </div>
        </div>

    </div>
</template>

<script>
    import PopupNavigation from './PopupNavigation.vue';

    export default {

        components: { PopupNavigation },
    }
</script>
