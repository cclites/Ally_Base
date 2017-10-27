<template>
    <div>
        <input
                ref="datepicker"
                :class="cssClass"
                type="text"
                v-model="value"
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
                if (this.invalidDate) {
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
            let selector = jQuery(component.$refs.datepicker);
            selector.datepicker(component.allOptions).on("changeDate", function() {
                // Keep vue value in sync
                component.$emit('input', selector.val());
            });
        }
    }
</script>
