<template>

    <b-modal ref="schedule-request-modal" title="Open Shift Requests" v-model="showModal" size="lg">

        <schedule-requests :selected-schedule-id=" selectedScheduleId " v-if=" selectedScheduleId " @request-response=" requestResponse "></schedule-requests>

        <div slot="modal-footer">

            <b-btn variant="info" @click=" showModal = false ">Close</b-btn>
        </div>
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
            }
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

        methods: {

            requestResponse( value ){

                this.$emit( 'request-response', value );
            }
        },

        components: {

            ScheduleRequests
        }
    }
</script>
