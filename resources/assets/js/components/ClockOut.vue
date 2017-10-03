<template>
    <b-card header="Clock Out"
        header-bg-variant="info"
        header-text-variant="white"
        >
        <form @submit.prevent="clockOut()" @keydown="form.clearError($event.target.name)">
            <b-row>
                <b-col lg="12">
                    <b-form-group label="Current Time" label-for="time">
                        <b-form-input v-model="time" readonly></b-form-input>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="6">
                    <b-form-group label="Recorded Mileage" label-for="mileage">
                        <b-form-input
                                id="mileage"
                                name="mileage"
                                type="number"
                                step="any"
                                v-model="form.mileage"
                        >
                        </b-form-input>
                        <input-help :form="form" field="mileage" text="Enter the number of miles driven during your shift."></input-help>
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Other Expenses" label-for="other_expenses">
                        <b-form-input
                                id="other_expenses"
                                name="other_expenses"
                                type="number"
                                step="any"
                                v-model="form.other_expenses"
                        >
                        </b-form-input>
                        <input-help :form="form" field="other_expenses" text="Enter the amount of expenses incurred during your shift."></input-help>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="12">
                    <b-form-group label="Were there any injuries on your shift?" label-for="injuries">
                        <b-form-select
                            id="injuries"
                            name="injuries"
                            >
                            <option>No</option>
                            <option>Yes</option>
                        </b-form-select>
                        <input-help :form="form" field="injuries" text="Indicate if you or someone else suffered an injury."></input-help>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="12">
                    <b-form-group label="Comments / Notes" label-for="caregiver_comments">
                        <b-form-textarea id="caregiver_comments"
                                         v-model="form.caregiver_comments"
                                         :rows="3"
                                         >
                        </b-form-textarea>
                        <input-help :form="form" field="other_expenses" text="Enter any important notes or comments about your shift."></input-help>
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
            'shift': {},
        },

        data() {
            return {
                form: new Form({
                    caregiver_comments: null,
                    mileage: 0,
                    other_expenses: 0.00,
                }),
            }
        },

        mounted() {

        },

        methods: {
            clockOut() {
                this.form.post('/clock-out')
                    .then(function(response) {
                        window.location = '/clock-in'
                    });
            }
        },

        computed: {
            time() {
                return moment().local().format('LT');
            }
        }


    }
</script>
