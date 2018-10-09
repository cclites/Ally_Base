<template>
    <div id="avatar-box" class="avatar mb-2" :style="styles">
        <img :src="value" />
    </div>
</template>

<script>
    export default {
        props: {
            value: {
                type: String,
                default: '',
            },
            size: {
                type: String,
                default: '150',
            },
        },

        computed: {
            styles() {
                return `width: ${this.size}px !important; height: ${this.size}px !important;`
            },
        },

        data: () => ({
        }),

        methods: {
        },

        mounted() {
        },
    }
</script>
