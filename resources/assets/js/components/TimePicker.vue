<template>
    <div>
        <input
                ref="timepicker"
                :class="cssClass"
                type="text"
                v-model="value"
                @change="onChange($event.target.value, $event)"
        />
        <b-tooltip :target="$refs.timepicker" title="Invalid time format (Example: 12:00 PM)" placement="top" v-if="invalidTime"></b-tooltip>
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
            'format': {
                default() {
                    return 'h:mm A';
                }
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
                return !moment(this.value, this.format, true).isValid();
            }
        },

        methods: {
            onChange(value, e) {
                this.$emit('input', value);
            },
        }
    }
</script>
