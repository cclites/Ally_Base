<template>
    <div class="avatar circle" :style="{ width: size+'px!important', height: size+'px!important' }">
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

        data: () => ({
        }),

        methods: {
        },

        mounted() {
        },
    }
</script>
