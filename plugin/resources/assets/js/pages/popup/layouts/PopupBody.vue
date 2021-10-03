<template>
    <div class="columns is-gapless popup-body">

        <div class="column" id="page-right">
            <div class="page-container">
                <keep-alive>
                    <router-view :key="$route.path"/>
                </keep-alive>
            </div>
        </div>

    </div>
</template>
