<template>
    <div class="popup" v-if="shift.id" id="schedule-item-popup">
        test test test
    </div>
</template>

<script>
export default {
    props: {
        shift: Object,
    },

    data: () => ({
    }),

    methods: {
    },

    watch: {
        shift(newVal) {
            if (newVal.id) {
            }
        },
    },
}
</script>
