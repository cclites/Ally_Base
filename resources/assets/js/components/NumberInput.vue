<template>
    <input
        v-model="val"
        type="text"
        class="form-control"
        @input="onChange()"
    />
</template>

<script>
    export default {
        props: {
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
                pattern: /[0-9\.\-]+/,
                val: '',
            };
        },

        methods: {
            onChange() {
                this.val = this.getNumbersOnly(this.val);
                this.$emit('input', this.val);
            },

            getNumbersOnly(str) {
                let matches = str.match(this.pattern);

                if (! matches) {
                    return '';
                }

                return matches[0];
            },
        },

        watch: {
            value(newValue, oldValue) {
                this.val = this.getNumbersOnly(newValue);
            },
        },

        created() {
            this.val = this.getNumbersOnly(this.value);
        },
    }
</script>
