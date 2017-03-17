<template>
    <div class="columns is-gapless popup-body">

        <popup-navigation :context="context"></popup-navigation>

        <div class="column page-right">
            <div class="page-container">
                <keep-alive>
                    <router-view :context="context"></router-view>
                </keep-alive>
            </div>
        </div>

    </div>
</template>

<script>
    import { PopupNavigation } from './';

    export default {

        components: { PopupNavigation },

        props: {
            context: { required: true },
        }
    }
</script>
