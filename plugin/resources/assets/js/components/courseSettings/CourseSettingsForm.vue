<template>
    <div class="">

        <charon-fieldset>
            <template slot="title">{{ tester_settings_title }}</template>

            <slot>
                <h2>Hello Worldo!</h2>
            </slot>

        </charon-fieldset>

        <charon-fieldset>
            <template slot="title">{{ presets_title }}</template>

            <slot>
                <h2>More Hello Worlds!</h2>
            </slot>
        </charon-fieldset>

    </div>
</template>

<script>
    import CharonFieldset from './../form/CharonFieldset.vue';

    export default {
        props: [
            'tester_settings_title', 'presets_title'
        ],

        components: {
            CharonFieldset
        }
    }
</script>
