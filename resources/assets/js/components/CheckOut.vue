<template>
    <b-card header="Check In"
        header-bg-variant="info"
        header-text-variant="white"
        >
        <form @submit.prevent="checkOut()" @keydown="form.clearError($event.target.name)">
            <b-row>
                <b-col lg="12">
                    <b-form-group label="Current Time" label-for="time">
                        <b-form-input v-model="time" readonly></b-form-input>
                    </b-form-group>
                    <b-form-group label="Current Shift" label-for="schedule_id">
                        <b-form-select
                            id="schedule_id"
                            name="schedule_id"
                            v-model="form.schedule_id"
                            readonly
                            >
                            <option value="1">Demo Client - 10:00AM</option>

                        </b-form-select>
                        <input-help :form="form" field="" text=""></input-help>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="12">
                    <b-button id="save-profile" variant="success" type="submit">I am finished with my shift.</b-button>
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
                    schedule_id: 1,
                }),
                countries: new Countries()
            }
        },

        mounted() {

        },

        methods: {

            checkOut() {
                window.location = '/check-in?checked_out=1';
            }

        },

        computed: {
            time() {
                return moment().local().format('LT');
            }
        }


    }
</script>
