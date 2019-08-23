<template>
        <input
                ref="datepicker"
                :class="cssClass"
                type="text"
                v-model="localValue"
                :name="name"
                :placeholder="placeholder"
                :disabled="disabled"
                :required="required"
                :readonly="readonly"
                @change="updateInput()"
        />
</template>

<script>
    export default {
        props: {
            value: {
                type: String,
                default: '',
            },
            format: {
                type: String,
                default: 'MM/DD/YYYY',
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
            },
            options: {
                type: Object,
            },
            'name': {
                type: String,
                default: ''
            },
        },

        data() {
            return {
                localValue: this.value,
                defaultOptions: {
                    forceParse: false,
                    autoclose: true,
                    todayHighlight: true,
                    format: this.format.toLowerCase(),
                },
            }
        },

        computed: {
            allOptions() {
                let allOptions = {};
                // merge defaultOptions and options
                return Object.assign(allOptions, this.defaultOptions, this.options);
            },
            cssClass() {
                let classes = 'form-control datepicker';
                if (this.value !== '' && this.invalidDate) {
                    classes = classes + ' is-invalid';
                }
                return classes;
            },
            invalidDate() {
                return (this.required || this.value) && !moment(this.value, this.format, true).isValid();
            }
        },

        mounted() {
            let component = this;
            this.selector().datepicker(component.allOptions).on("changeDate", function() {
                // Keep vue value in sync
                component.updateInput();
            });
        },

        methods: {
            updateInput() {
                this.localValue = this.selector().val();
                this.$emit('input', this.selector().val());
            },
            selector() {
                return jQuery(this.$refs.datepicker);
            }
        },

        watch: {
            value(newVal, oldVal) {
                if (newVal !== oldVal){
                    this.localValue = newVal;
                    // Update the datepicker's highlighted date when external value changes occur
                    this.selector().datepicker('update', newVal);
                }
            },
        }
    }
</script>
