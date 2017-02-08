<template>
    <p class="control">
        <span class="select">
            <select name="charon" id="charon-select" v-model="selected" @change="onCharonSelected">
                <option v-for="charon in charons" :value="charon.id">
                    {{ charon.name }}
                </option>
            </select>
        </span>
    </p>
</template>

<script>
    export default {
        props: {
            charons: { required: true }
        },

        data() {
            return {
                selected: this.charons.length > 0 ? this.charons[0].id : null
            };
        },

        methods: {
            onCharonSelected(event) {
                VueEvent.$emit('charon-was-changed', event.target.value);
            }
        }
    }
</script>
