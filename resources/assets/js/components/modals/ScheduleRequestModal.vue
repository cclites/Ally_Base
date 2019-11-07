<template>

    <b-modal id="detailsModal" title="Open Shift Requests" v-model="showModal" size="lg">

        <schedule-requests :selected-schedule-id=" selectedScheduleId " v-if=" selectedScheduleId "></schedule-requests>
    </b-modal>
</template>

<script>

    import ScheduleRequests from '../business/schedule/ScheduleRequests';

    export default {

        props: {

            value: false,
            selectedScheduleId: {

                type: Number,
                default: null,
            },
        },

        computed: {

            showModal: {

                get() {

                    return this.value;
                },
                set( value ) {

                    this.$emit( 'input', value );
                }
            },
        },

        components: {

            ScheduleRequests
        }
    }
</script>
