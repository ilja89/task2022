<template>
    <h1>Simple task info section here!</h1>
</template>

<script>
    import CharonSelect from '../form/CharonSelect.vue';

    import Translate from '../../mixins/translate';
    import EmitEventOnInputChange from '../../mixins/emitEventOnInputChange';

    export default {
        mixins: [ Translate, EmitEventOnInputChange ],

        components: { CharonSelect },

        props: [ 'form' ],
    }
</script>
