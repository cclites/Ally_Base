<template>
    <div>
        <mask-input
                v-if="usingIE"
                :class="cssClass"
                type="time"
                :disabled="disabled"
                :required="required"
                :readonly="readonly"
                v-model="localValue"
                :id="id"
        />
        <input
                v-else
                :class="cssClass"
                type="time"
                :disabled="disabled"
                :required="required"
                :readonly="readonly"
                v-model="localValue"
                :id="id"
        />
    </div>
</template>

<script>
    import InternetExplorer from "../mixins/InternetExplorer";

    export default {
        mixins: [InternetExplorer],

        props: {
            value: {
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
            id: {
                type: String,
                default: '',
            },
        },

        data() {
            return {
                localValue: this.value,
                format: 'HH:mm'
            }
        },

        mounted() {

        },

        computed: {
            cssClass() {
                let classes = 'form-control timepicker';
                if (this.invalidTime) {
                    classes = classes + ' is-invalid';
                }
                return classes;
            },
            invalidTime() {
                return (this.required || this.value) && !moment(this.value, this.format, true).isValid();
            }
        },

        watch: {
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
        }
    }
</script>
