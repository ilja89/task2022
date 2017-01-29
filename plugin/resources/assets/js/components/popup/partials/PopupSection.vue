<template>
    <div class="section">

        <div class="section-header bottom-border-separator">
            <div class="section-header-text">
                <h4 class="title is-4">{{ title }}</h4>
                <h6 class="subtitle is-6">{{ subtitle }}</h6>
            </div>
            <div class="section-header-right">
                <slot name="header-right"></slot>
            </div>
        </div>

        <div class="section-body">
            <slot></slot>
        </div>

    </div>
</template>

<script>
    export default {
        props: {
            title: { required: true },
            subtitle: {
                required: false,
                default: ''
            }
        }
    }
</script>
