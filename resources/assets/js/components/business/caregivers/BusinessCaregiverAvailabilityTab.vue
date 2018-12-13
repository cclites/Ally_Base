<template>
    <b-row>
        <b-col lg="12">
            <b-card header="Availability"
                    header-bg-variant="info"
                    header-text-variant="white"
                    id="availability-tab"
            >
                <b-row>
                    <b-col lg="12">
                        <b-form-group label="Available Days">
                            <label class="custom-control custom-checkbox" v-for="day in daysOfWeek" :key="day">
                                <input type="checkbox" class="custom-control-input" v-model="form[day]" :true-value="1" :false-value="0">
                                <span class="custom-control-indicator"></span>
                                <span class="custom-control-description">{{ day | capitalize }}</span>
                            </label>
                        </b-form-group>
                    </b-col>
                    <b-col lg="6">
                        <b-form-group label="Available Times">
                            <label class="custom-control custom-checkbox" v-for="time in timesOfDay" :key="time">
                                <input type="checkbox" class="custom-control-input" v-model="form[time]" :true-value="1" :false-value="0">
                                <span class="custom-control-indicator"></span>
                                <span class="custom-control-description">{{ time | capitalize }}</span>
                            </label>
                        </b-form-group>
                    </b-col>
                    <b-col lg="6">
                        <b-form-group label="Willing to Perform Live In">
                            <b-form-radio-group v-model="form.live_in">
                                <b-form-radio :value="0">No</b-form-radio>
                                <b-form-radio :value="1">Yes</b-form-radio>
                            </b-form-radio-group>
                        </b-form-group>
                    </b-col>
                    <b-col lg="6">
                        <b-form-group label="Preferred Shift Length">
                            <b-row>
                                <b-col cols="6">
                                    <label>Minimum Hours: <b-form-input size="sm" v-model="form.minimum_shift_hours" type="number" step="1" /></label>
                                </b-col>
                                <b-col cols="6">
                                    <label>Maximum Hours: <b-form-input size="sm" v-model="form.maximum_shift_hours" type="number" step="1" /></label>
                                </b-col>
                            </b-row>
                        </b-form-group>
                    </b-col>
                    <b-col lg="6">
                        <b-form-group label="How many miles are they willing to travel?">
                            <b-form-radio-group v-model="form.maximum_miles">
                                <b-form-radio :value="5">5</b-form-radio>
                                <b-form-radio :value="10">10</b-form-radio>
                                <b-form-radio :value="15">15</b-form-radio>
                                <b-form-radio :value="20">20</b-form-radio>
                                <b-form-radio :value="100">&gt;20</b-form-radio>
                            </b-form-radio-group>
                        </b-form-group>
                    </b-col>
                </b-row>
                <hr />
                <b-form-group label="Other Preferences">
                    <b-form-textarea v-model="form.preferences" rows="3"></b-form-textarea>
                </b-form-group>
                <b-form-group>
                    <b-btn @click="updatePreferences" variant="success">Save</b-btn>
                </b-form-group>
            </b-card>
        </b-col>
        <b-col lg="12" v-if="! isCaregiver && updatedBy">
            <b-card>
                Last updated {{ formatDateFromUTC(caregiver.availability.updated_at) }} by {{ updatedBy }}
            </b-card>
        </b-col>
    </b-row>
</template>

<script>
    import FormatsDates from "../../../mixins/FormatsDates";

    export default {
        props: ['caregiver', 'updatedBy'],

        mixins: [FormatsDates],

        data() {
            return{
                daysOfWeek: ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'],
                timesOfDay: ['morning', 'afternoon', 'evening', 'night'],
                form: new Form({
                    monday: this.caregiver.availability ? this.caregiver.availability.monday : 0,
                    tuesday: this.caregiver.availability ? this.caregiver.availability.tuesday : 0,
                    wednesday: this.caregiver.availability ? this.caregiver.availability.wednesday : 0,
                    thursday: this.caregiver.availability ? this.caregiver.availability.thursday : 0,
                    friday: this.caregiver.availability ? this.caregiver.availability.friday : 0,
                    saturday: this.caregiver.availability ? this.caregiver.availability.saturday : 0,
                    sunday: this.caregiver.availability ? this.caregiver.availability.sunday : 0,
                    morning: this.caregiver.availability ? this.caregiver.availability.morning : 0,
                    afternoon: this.caregiver.availability ? this.caregiver.availability.afternoon : 0,
                    evening: this.caregiver.availability ? this.caregiver.availability.evening : 0,
                    night: this.caregiver.availability ? this.caregiver.availability.night : 0,
                    live_in: this.caregiver.availability ? this.caregiver.availability.live_in : 0,
                    minimum_shift_hours: this.caregiver.availability ? this.caregiver.availability.minimum_shift_hours : 0,
                    maximum_shift_hours: this.caregiver.availability ? this.caregiver.availability.maximum_shift_hours : 24,
                    maximum_miles: this.caregiver.availability ? this.caregiver.availability.maximum_miles : 20,

                    preferences: this.caregiver.preferences
                })
            }
        },

        computed: {
            isCaregiver() {
                return window.AuthUser && window.AuthUser.role_type == 'caregiver';
            },
        },

        methods: {
            updatePreferences() {
                var url = '/business/caregivers/' + this.caregiver.id + '/preferences';

                if (this.isCaregiver) {
                    url = '/profile/preferences';
                }

                this.form.put(url);
            }
        }
    }
</script>

<style>
    #availability-tab .col-form-legend {
        font-weight: 500;
    }
</style>
