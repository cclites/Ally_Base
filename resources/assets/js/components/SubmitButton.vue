<template>
    <button :disabled="submitting" :class="buttonClasses">
        <i class="fa fa-spinner fa-spin" v-show="submitting"></i>
        <i v-if="icon" :class="icon" v-show="!submitting"></i>
        <slot></slot>
    </button>
</template>

<script>
    export default {
        name: "SubmitButton",

        props: {
            'submitting': {
                'default': false,
            },
            'icon': {
                'type': String,
                'default': '',
            },
            'type': {
                'type': String,
                'default': 'button'
            },
            'variant': {
                'type': String,
                'default': 'default',
            },
            'size': {
                'type': String,
                'default': ''
            }
        },

        computed: {
            buttonClasses() {
                let size = '';
                if (this.size) {
                    size = ` btn-${this.size}`
                }
                return `btn btn-${this.variant}${size}`;
            }
        }
    }
</script>

<style scoped>

</style>