<template>
    <div>
        <input
                ref="timepicker"
                :class="cssClass"
                type="text"
                :placeholder="placeholder"
                :disabled="disabled"
                :required="required"
                :readonly="readonly"
                v-model="value"
                @change="onChange($event.target.value, $event)"
        />
        <b-tooltip :target="$refs.timepicker" title="Invalid time format (Example: 12:00 PM)" placement="top" v-if="invalidTime"></b-tooltip>
    </div>
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
                default: 'h:mm A',
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

        data() {
            return {

            }
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

        methods: {
            onChange(value, e) {
                this.$emit('input', value);
            },
        }
    }
</script>
