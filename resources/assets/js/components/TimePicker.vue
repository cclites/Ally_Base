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
        />
        <input
                v-else
                :class="cssClass"
                type="time"
                :disabled="disabled"
                :required="required"
                :readonly="readonly"
                v-model="value"
                @change="onChange($event.target.value, $event)"
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
            format: {
                type: String,
                default: 'h:mm A',
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

        data() {
            return {
                localValue: this.value,
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
