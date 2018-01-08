<template>
    <div>
        <input
                ref="datepicker"
                :class="cssClass"
                type="text"
                v-model="value"
                :placeholder="placeholder"
                @change="updateInput()"
        />
    </div>
</template>

<script>
    export default {
        props: {
            'value': {
                default() {
                    return '';
                }
            },
            'options': {
                default() {
                    return {};
                },
            },
            'format': {
                default() {
                    return 'MM/DD/YYYY';
                }
            },
            'placeholder': {
                default() {
                    return '';
                }
            }
        },

        data() {
            return {
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
                return !moment(this.value, this.format, true).isValid();
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
                this.value = this.selector().val();
                this.$emit('input', this.selector().val());
            },
            selector() {
                return jQuery(this.$refs.datepicker);
            }
        },

        watch: {
            value(val) {
                // Update the datepicker's highlighted date when external value changes occur
                this.selector().datepicker('update', val);
            }
        }
    }
</script>
