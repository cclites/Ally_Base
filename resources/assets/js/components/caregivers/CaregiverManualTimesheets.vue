<template>
    <b-card header="Manual Timesheet"
            border-variant="info"
            header-bg-variant="info"
            header-text-variant="white">
        {{ this.caregiver.name }}
    </b-card>
</template>

<script>
    export default {
        props: ['caregiver'],

        mixins: [],

        components: {

        },

        data() {
            return{

            }
        },

        created() {

        },

        mounted() {

        },

        methods: {

        },

        computed: {

        }
    }
</script>