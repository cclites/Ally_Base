<template>
    <b-input
           :autocomplete="autocomplete"
           :id="id"
           :name="name"
           :placeholder="placeholderValue"
           :disabled="disabled"
           :required="required"
           :readonly="readonly"
           v-model="localValue"
           v-mask="maskConfig"
    ></b-input>
</template>

<script>
    const TYPES = [
        'phone','ssn','date'
    ];
    export default {
        data() {
            return {
                localValue: this.value,
                tokens: {
                    '#': {pattern: /\d/},
                    'N': {pattern: /[0-9a-zA-Z]/}, // Alphanumeric
                    'S': {pattern: /[a-zA-Z]/},
                    'A': {pattern: /[a-zA-Z]/, transform: v => v.toLocaleUpperCase()},
                    'a': {pattern: /[a-zA-Z]/, transform: v => v.toLocaleLowerCase()},
                    '!': {escape: true},
                    '*': {pattern: /[*0-9]/}, // Asterisk or number
                }
            }
        },

        props: {
            value: {
                default: null
            },
            type: {
                type: String,
                default: 'phone',
            },
            id: {
                type: String,
                default: '',
            },
            name: {
                type: String,
                default: '',
            },
            placeholder: {
                type: String,
                default: '',
            },
            disabled: {
                type: Boolean,
                default: false
            },
            readonly: {
                type: Boolean,
                default: false
            },
            required: {
                type: Boolean,
                default: false
            }
        },

        mounted() {

        },

        computed: {
            autocomplete() {
                switch (this.type) {
                    case 'ssn':
                        return 'off';
                    default:
                        return 'on';
                }
            },
            maskConfig() {
                switch (this.type) {
                    case 'phone':
                        return {
                            'mask': '(###) ###-####',
                            'tokens': this.tokens
                        };
                    case 'ssn':
                        return {
                            'mask': '***-**-****',
                            'tokens': this.tokens,
                        };
                    case 'date':
                        return {
                            'mask': '##/##/####',
                            'tokens': this.tokens
                        };
                    case 'time':
                        return {
                            'mask': '0#:5#',
                            'tokens': {
                                '0': {pattern: /[0-2]/},
                                '5': {pattern: /[0-5]/},
                                '#': {pattern: /\d/},
                                'X': {pattern: /[AaPp]/, transform: v => v.toLocaleUpperCase()},
                            }
                        }
                }
            },
            placeholderValue() {
                if (this.disabled || this.readonly) return '';
                if (this.placeholder) return this.placeholder;
                switch (this.type) {
                    case 'date':
                        return 'MM/DD/YYYY';
                    case 'time':
                        return '13:00 (24-Hour on IE)';
                }
                return '';
            }
        },

        watch:{
            value(newVal, oldVal) {
                if (newVal !== oldVal){
                    this.localValue = newVal;
                }
            },
            localValue(newVal, oldVal) {
                if (newVal !== oldVal){
                    this.$emit('input', newVal);
                }
            }
        },

        methods: {

        }
    }
</script>