<template>
    <b-form-group :label="label" class="form-check">
        <label class="custom-control custom-checkbox" v-for="(label, val) in items" :key="val">
            <input type="checkbox" class="custom-control-input" v-model="selectedItems" :value="val" @change="update()">
            <span class="custom-control-indicator"></span>
            <span class="custom-control-description">{{ label }}</span>
        </label>
    </b-form-group>
</template>

<script>
    export default {
        props: {
            value: Array,
            items: Object,
            label: String,
        },

        data() {
            return {
                selectedItems: [],
            }
        },

        mounted() {
            this.selectedItems = this.value;
        },

        methods: {
            update(event) {
                this.$emit('input', this.selectedItems);
            }
        },

        watch: {
            value(newVal) {
                this.selectedItems = newVal;
            }
        }
    }
</script>

<style scoped>
    .custom-checkbox {
        align-items: center;
    }
</style>