<template>
    <b-form-select v-model="selectedType" :options="clientTypes" @input="onChange()">
        <template slot="first">
            <option value="">{{ emptyText }}</option>
        </template>
    </b-form-select>
</template>

<script>
    import Constants from "../../mixins/Constants";
    export default {
        mixins: [Constants],

        props: {
            /**
             * The text to display for the Empty item.
             */
            emptyText: {
                type: String,
                default: 'All Client Types',
            },
            /**
             * The starting selected value.
             */
            value: {
                type: String,
                default: '',
            },
        },

        data() {
            return {
                selectedType: '',
            };
        },

        methods: {
            onChange() {
                this.$emit('input', this.selectedType);
            },
        },

        watch: {
            value(newValue, oldValue) {
                this.selectedType = newValue;
            },
        },

        created() {
            this.selectedType = this.value;
        },
    }
</script>
