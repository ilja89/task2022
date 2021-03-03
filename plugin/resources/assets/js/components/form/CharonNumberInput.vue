<template>
    <div :id="'id_' + name + '_container'" class="fcontainer clearfix">
        <div :id="'fitem_id_' + name" class="fitem fitem_ftext" :class="[ requiredClass ]">
            <div class="fitemtitle">
                <label :for="'id_' + name" :class="[ requiredClass ]">{{ label }}</label>
            </div>
            <p class="input-helper" v-if="helper_text !== null" v-html="helper_text"></p>
            <div class="felement ftext">
                <input class="form-control" :class="[ input_class ]"
                       :id="'id_' + name"
                       :name="name"
                       type="number"
                       :required="required"
                       step="0.01"
                       v-model="input_value"
                       @keyup="onInputChanged">
            </div>
        </div>
    </div>
</template>

<script>
    import { FormElement } from '../../mixins'

    export default {
        mixins: [ FormElement ],
    }
</script>

<style lang="scss" scoped>

    @mixin is-full-width-mobile {
        @media screen and (max-width: 768px) {
            width: 100%;
        }
    }

    .form-control {

        &.is-quarter {
            width: 25%;
            @include is-full-width-mobile;
        }

        &.is-half {
            width: 50%;
            @include is-full-width-mobile;
        }
    }

</style>
