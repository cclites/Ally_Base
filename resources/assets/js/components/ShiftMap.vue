<template>
    <div>
        <GmapMap class="shift-map"
                :center="position"
                :options="options"
                :zoom="13"
                map-type-id="roadmap"
        >
            <GmapMarker
                :position="position"
                :draggable="false"
            />
            <GmapCircle
                :center="addressPosition"
                :draggable="false"
                :editable="false"
                :radius="circleRadius"
                :options="circleOptions"
                v-if="addressPosition"
            />
        </GmapMap>
        <div v-if="noAddress" class="text-muted">
            Note: No EVV Address set for this shift.
        </div>
    </div>
</template>

<script>
    export default {
        props: {
            lat: String,
            lng: String,
            address: Object,
            circleRadius: {
                type: Number,
                default: 300,
            },
        },

        computed: {
            circleOptions() {
                return {
                    fillColor: '#000099',
                    fillOpacity: 0.2,
                    strokeColor: '#000044',
                    strokeOpacity: 0.8,
                }
            },
            options() {
                return {
                    fullscreenControl: false,
                    mapTypeControl: false,
                    panControl: false,
                    rotateControl: false,
                    scaleControlOptions: false,
                    scrollwheel: false,
                    streetViewControl: false,
                }
            },
            position() {
                return {
                    lat: parseFloat(this.lat),
                    lng: parseFloat(this.lng),
                }
            },
            addressPosition() {
                if (this.noAddress) {
                    return null;
                }

                return {
                    lat: parseFloat(this.address.latitude),
                    lng: parseFloat(this.address.longitude),
                }
            },
            noAddress() {
                return ! this.address || ! this.address.latitude || ! this.address.longitude;
            },
        },

        mounted() {

        },

        methods: {},
    }
</script>

<style>
    .shift-map {
        width: 100%;
        height: 200px;
    }
</style>
