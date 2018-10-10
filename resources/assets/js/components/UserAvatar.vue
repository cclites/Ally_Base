<template>
    <div id="avatar-box" class="avatar" :style="styles">
        <img :src="src" :title="title" alt="" />
    </div>
</template>

<script>
    export default {
        props: {
            src: {
                type: String,
                default: '',
            },
            title: {
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
