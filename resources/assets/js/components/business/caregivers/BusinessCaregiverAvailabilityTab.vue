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
                </b-row>
                <b-row>
                    <b-col lg="3">
                        <b-form-group label="Preferred Shift Start Time" label-for="available_start_time">
                            <time-picker
                                    id="available_start_time"
                                    name="available_start_time"
                                    v-model="form.available_start_time"
                            />
                            <input-help :form="form" field="available_start_time" text="" />
                        </b-form-group>
                    </b-col>
                    <b-col lg="3">
                        <b-form-group label="Preferred Shift End Time" label-for="available_end_time">
                            <time-picker
                                    id="available_end_time"
                                    name="available_end_time"
                                    v-model="form.available_end_time"
                            />
                            <input-help :form="form" field="available_end_time" text="" />
                        </b-form-group>
                    </b-col>

                    <b-col lg="6">
                        <b-form-group label="Willing to Perform Live-In">
                            <b-form-radio-group v-model="form.live_in" class="mt-3">
                                <b-form-radio :value="0">No</b-form-radio>
                                <b-form-radio :value="1">Yes</b-form-radio>
                            </b-form-radio-group>
                        </b-form-group>
                    </b-col>
                </b-row>
                <b-row>
                    <b-col lg="6">
                        <b-form-group label="Preferred Shift Length">
                            <b-row>
                                <b-col cols="6">
                                    <b-form-group label="Minimum Hours:">
                                        <b-form-input size="sm" v-model="form.minimum_shift_hours" type="number" step="1" />
                                    </b-form-group>
                                </b-col>
                                <b-col cols="6">
                                    <b-form-group label="Maximum Hours:">
                                        <b-form-input size="sm" v-model="form.maximum_shift_hours" type="number" step="1" />
                                    </b-form-group>
                                </b-col>
                            </b-row>
                        </b-form-group>
                    </b-col>
                    <b-col lg="6" class="mt-4">
                        <b-form-group label="How many miles are they willing to travel?">
                            <b-form-radio-group v-model="form.maximum_miles" class="mt-2">
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
                <b-alert variant="warning" :show="showWarning" dismissible>
                    There is already an entry for the selected days.
                </b-alert>
                <b-form-group label="Specific Days Not Available to be Referred / Vacation Days">
                    <p class="text-danger">Please click 'Add' <u>before</u> saving preferences.</p>
                    <b-form inline class="mb-2 align-items-baseline" @submit.prevent="addDayOff()">
                        <label for="dayoff_start" class="mr-2">Date</label>
                        <date-picker v-model="dayoff_start" id="dayoff_date_start" class="mb-2 mr-2" placeholder="Select Start" required></date-picker>
                        <date-picker v-model="dayoff_end" id="dayoff_date_end" class="mb-2 mr-2" placeholder="Select End"></date-picker>

                        <label for="dayoff_reason" class="mr-2 ml-4">Description</label>
                        <b-input type="text" v-model="dayoff_reason" id="dayoff_reason" class="mb-2 mr-2" maxlength="156" required />
                        <b-button variant="info" type="submit">Add</b-button>
                    </b-form>
                    <div class="table-responsive">
                        <b-table bordered striped hover show-empty
                            :items="form.days_off"
                            :fields="daysOffFields"
                            sort-by="date"
                            :sort-desc="false"
                        >
                            <template slot="actions" scope="row">
                                <b-btn size="sm" variant="danger" @click="removeDayOff(row.index)">Remove</b-btn>
                            </template>
                        </b-table>
                    </div>
                </b-form-group>
                <hr />
                <b-form-group label="Other Preferences">
                    <b-form-textarea v-model="form.preferences" rows="3"></b-form-textarea>
                </b-form-group>
                <b-form-group>
                    <b-btn @click="updatePreferences" variant="success">Save Availability Preferences</b-btn>
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
            return {
                // reasons: {
                //     family: 'Will be away with family',
                //     other: 'Other',
                // },
                showWarning: false,
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
                    available_start_time: this.caregiver.availability.available_start_time ? this.caregiver.availability.available_start_time.slice(0,5) : '',
                    available_end_time: this.caregiver.availability.available_end_time ? this.caregiver.availability.available_end_time.slice(0,5) : '',
                    morning: this.caregiver.availability ? this.caregiver.availability.morning : 0,
                    afternoon: this.caregiver.availability ? this.caregiver.availability.afternoon : 0,
                    evening: this.caregiver.availability ? this.caregiver.availability.evening : 0,
                    night: this.caregiver.availability ? this.caregiver.availability.night : 0,
                    live_in: this.caregiver.availability ? this.caregiver.availability.live_in : 0,
                    minimum_shift_hours: this.caregiver.availability ? this.caregiver.availability.minimum_shift_hours : 0,
                    maximum_shift_hours: this.caregiver.availability ? this.caregiver.availability.maximum_shift_hours : 24,
                    maximum_miles: this.caregiver.availability ? this.caregiver.availability.maximum_miles : 20,
                    preferences: this.caregiver.preferences,
                    days_off: this.caregiver.days_off ? this.caregiver.days_off : [],
                }),
                dayoff_reason: '',
                dayoff_start: '',
                dayoff_end: '',
                daysOffFields: [
                    { key: 'start_date', label: 'Start Date', sortable: true, formatter: x => moment(x).format('M/D/YY') },
                    { key: 'end_date', label: 'End Date', sortable: true, formatter: x => moment(x).format('M/D/YY') },
                    { key: 'description', label: 'Description', sortable: true },
                    { key: 'actions', label: ' ', sortable: false },
                ],
            }
        },

        computed: {
            isCaregiver() {
                return window.AuthUser && window.AuthUser.role_type == 'caregiver';
            },
        },

        methods: {
            addDayOff() {
                let formattedStartDate = moment(this.dayoff_start).format('YYYY-MM-DD');

                if(!this.dayoff_end){
                    this.dayoff_end = this.dayoff_start;
                }
                let formattedEndDate = moment(this.dayoff_end).format('YYYY-MM-DD');

                if (this.form.days_off.findIndex(x => x.start_date == formattedStartDate && x.end_date == formattedEndDate) < 0) { // skip duplicates
                    this.form.days_off.push(
                        { start_date: formattedStartDate, end_date: formattedEndDate, description: this.dayoff_reason }
                    );
                }else{
                    this.showWarning = true;
                }

                this.dayoff_start = '';
                this.dayoff_end = '';
                this.dayoff_reason = '';
            },
            removeDayOff(index) {
                this.form.days_off.splice(index, 1);
            },
            updatePreferences() {
                var url = '/business/caregivers/' + this.caregiver.id + '/preferences';

                if (this.isCaregiver) {
                    url = '/profile/preferences';
                }

                this.form.put(url);
            },
        }
    }
</script>

<style>
    #availability-tab .col-form-legend {
        font-weight: 500;
    }
</style>
