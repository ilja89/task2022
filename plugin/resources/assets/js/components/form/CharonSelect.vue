<template>
    <div :id="'id_' + name + '_container'" class="fcontainer clearfix">
        <div :id="'fitem_id_' + name" class="fitem fitem_select">
            <div class="fitemtitle"><label :for="'id_' + name">{{ label }}</label></div>
            <div class="felement">
                <select :name="name"
                        :id="'id_' + name"
                        v-model="value"
                        @change="onChange">
                    <option
                            v-for="option in options"
                            :value="option[key_field]"
                            :selected="option[key_field] == selected ? 'selected' : ''">
                        {{ option.name }}
                    </option>
                </select>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: {
            label: { required: false, default: '' },
            name: { required: true },
            options: { required: true },
            selected: { required: true },
            key_field: { required: false, default: 'code' }
        },

        data() {
            return {
                value: this.selected == '' ? this.options[0][this.key_field] : this.selected
            };
        },

        watch: {
            selected() {
                this.value = this.selected;
            }
        },

        methods: {
            onChange() {
                this.$emit('input-was-changed', this.value);
            }
        },
    }
</script>
