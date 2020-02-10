<<<<<<< HEAD
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

=======