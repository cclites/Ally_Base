<template>
    <div>
        <h3 class="mb-3" v-if="!hideTitle">{{ item.title }}</h3>

        <div v-if="item.body" class="mb-3" v-html="item.body"></div>

        <div v-if="item.video_attachment_id" class="text-center">
            <!-- video element -->
            <vue-plyr>
                <video :src="item.video.url">
                    <source :src="item.video.url" type="video/mp4" size="720">
                </video>
            </vue-plyr>
        </div>

        <div v-if="item.attachments.length > 0" class="mt-5">
            <hr />
            <label>Attachments</label>
            <b-row>
                <b-col lg="6" v-for="attachment in item.attachments" :key="attachment.id" class="p-3 mb-1">
                    <a :href="attachment.url">{{ attachment.name }}</a>
                </b-col>
            </b-row>
        </div>
    </div>
</template>

<script>
    export default {
        props: ['item', 'hideTitle'],

        data() {
            return {
            };
        },

        computed: {
        },

        methods: {
        },

        mounted() {
        },
    }
</script>

<style>
    video {
        max-height: 580px;
    }
    iframe {
        width: 100%;
    }
    .aspect-ratio {
        position: relative;
        width: 100%;
        height: 0;
        padding-bottom: 51%;
    }
    .aspect-ratio iframe {
        position: absolute;
        width: 100%;
        height: 100%;
        left: 0; top: 0;
    }
</style>