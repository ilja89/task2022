<template>
    <div :id="'id_' + name + '_container'" class="fcontainer clearfix">
        <div :id="'fitem_id_' + name" class="fitem fitem_select">
            <div class="fitemtitle"><label :for="'id_' + name">{{ label }}</label></div>
            <div class="felement">
                <select :name="name"
                        :id="'id_' + name"
                        v-model="value"
                        @change="onChange"
                >
                    <option
                            v-for="option in options"
                            :value="option.code"
                            :selected="option.code == selected ? 'selected' : ''"
                    >
                        {{ option.name }}
                    </option>
                </select>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: [
            'label', 'name', 'options', 'selected'
        ],

        methods: {
            onChange() {
                this.$emit('input-was-changed', this.value);
            }
        },

        data() {
            return {
                value: this.selected == '' ? this.options[0].code : this.selected
            };
        }
    }
</script>
