<template>
    <b-card header="Clock In"
        header-bg-variant="info"
        header-text-variant="white"
        >
        <form @submit.prevent="clockIn()" @keydown="form.clearError($event.target.name)">
            <b-row>
                <b-col lg="12">
                    <b-form-group label="Current Time" label-for="time">
                        <b-form-input v-model="time" readonly></b-form-input>
                    </b-form-group>
                    <b-form-group label="Select the shift you are checking in for." label-for="schedule_id">
                        <b-form-select
                            id="schedule_id"
                            name="schedule_id"
                            v-model="form.schedule_id"
                            >
                            <option>Demo Client - 10:00AM</option>

                        </b-form-select>
                        <input-help :form="form" field="" text=""></input-help>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="12">
                    <b-button id="save-profile" variant="success" type="submit">Complete Check In</b-button>
                </b-col>
            </b-row>
        </form>
    </b-card>
</template>

<script>
    export default {
        props: {
            'title': '',
            'type': '',
            'user': null,
            'address': null,
            'action': null,
        },

        data() {
            return {
                form: new Form({
                    schedule_id: null,
                }),
            }
        },

        mounted() {

        },

        methods: {

            clockIn() {
                window.location = '/clock-out';
            }

        },

        computed: {
            time() {
                return moment().local().format('LT');
            }
        }


    }
</script>
