<template>
    <div :id="`id_${name}_container`" class="fcontainer clearfix">
        <div :id="`fitem_id_${name}`" class="fitem fitem_ftext" :class="[ requiredClass ]">
            <div class="fitemtitle">
                <label :for="`id_${name}`" :class="[ requiredClass ]">{{ label }}</label>
            </div>
            <p class="input-helper" v-if="helper_text !== null" v-html="helper_text"></p>
            <div class="felement ftext">
                <textarea
                    class="form-control" :class="[ input_class ]"
                    :id="`id_${name}`"
                    :name="name"
                    :required="required"
                    :autocomplete="autocomplete ? 'on' : 'off'"
                    v-model="input_value"
                    @keyup="onInputChanged"
                ></textarea>
            </div>
        </div>
    </div>
</template>

<script>
    import { FormElement } from '../../mixins'

    export default {

        mixins: [ FormElement ],

        props: {
            autocomplete: { required: false, default: true },
        }
    }
</script>
