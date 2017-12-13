<template>
    <div class="select">
        <select name="charon" id="charon-select" v-model="selected" @change="onCharonSelected">
            <option v-for="charon in charons" :value="charon.id">
                {{ charon.name }}
            </option>
        </select>
    </div>
</template>

<script>
    import { Charon } from '../../../models';

    export default {
        props: {
            active_charon: { required: true }
        },

        data() {
            return {
                selected: null,
                charons: []
            };
        },

        computed: {
            activeCharon() {
                let activeCharon = null;
                this.charons.forEach(charon => {
                    if (charon.id === this.selected) {
                        activeCharon = charon;
                    }
                });
                return activeCharon;
            }
        },

        mounted() {
            this.refreshCharons();
            VueEvent.$on('refresh-page', () => this.refreshCharons());
        },

        methods: {
            onCharonSelected() {
                this.$emit('charon-was-changed', this.activeCharon);
            },

            refreshCharons() {
                Charon.all(window.course_id, charons => {
                    this.charons = charons;
                    if (this.activeCharon === null && charons.length > 0) {
                        this.selected = charons[0].id;
                        this.onCharonSelected();
                    }
                });
            }
        }
    }
</script>
