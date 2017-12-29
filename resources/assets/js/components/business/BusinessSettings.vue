<template>
    <b-card
            header="Provider Settings"
            header-text-variant="white"
            header-bg-variant="info"
    >
        <b-row>
            <b-col lg="6">                
                <b-form-group label="Mileage Rate" label-for="mileageRate">
                    <b-form-input type="number"
                                  step="any"
                                  id="mileageRate"
                                  v-model="form.mileage_rate"
                    >
                    </b-form-input>
                    <input-help :form="form" field="mileageRate" text="Enter the amount reimbursed for each mile, 0 will disable mileage reimbursements"></input-help>
                </b-form-group>
                <b-form-group label="Auto-Confirm Shifts" label-for="auto_confirm">
                    <b-form-select id="auto_confirm" 
                                   v-model="form.auto_confirm"
                    >
                        <option value="0">No</option>
                        <option value="1">Yes</option>
                    </b-form-select>
                    <input-help :form="form" field="auto_confirm" text="Automatically confirm shifts that are clocked in on the app or telefony."></input-help>
                </b-form-group>
            </b-col>
            <b-col lg="6">
                <b-form-group label="Scheduling" label-for="scheduling">
                    <b-form-select id="scheduling" 
                                   v-model="form.scheduling"
                                   disabled="true"
                    >
                        <option value="1">Enabled</option>
                        <option value="0">Disabled</option>
                    </b-form-select>
                    <input-help :form="form" field="scheduling" text="Enable or disable shift scheduling functionality"></input-help>
                </b-form-group>

                <b-form-group label="Calendar Default View" label-for="calendar_default_view">
                    <b-form-select id="calendar_default_view"
                                   v-model="form.calendar_default_view"
                    >
                        <option value="month">Month</option>
                        <option value="agendaWeek">Week</option>
                    </b-form-select>
                    <input-help :form="form" field="calendar_default_view" text="Choose the default view for the Business Schedule"></input-help>
                </b-form-group>
                <b-form-group label="Default Caregiver Filter" label-for="calendar_caregiver_filter">
                    <b-form-select id="calendar_caregiver_filter"
                                   v-model="form.calendar_caregiver_filter"
                    >
                        <option value="all">All Caregivers</option>
                        <option value="unassigned">Unassigned Shifts</option>
                    </b-form-select>
                    <input-help :form="form" field="calendar_caregiver_filter" text="Choose the default caregiver filter for the Business Schedule"></input-help>
                </b-form-group>
            </b-col>
        </b-row>
        <b-row>
            <b-col lg="12">
                <b-form-group>
                    <b-button @click="update" variant="info">Save</b-button>
                </b-form-group>
            </b-col>
        </b-row>
    </b-card>
</template>

<style lang="scss">
</style>

<script>
    export default {
        props: {
            'business': Object,
        },

        data() {
            return {
                form: new Form({
                    scheduling: this.business.scheduling,
                    mileage_rate: this.business.mileage_rate,
                    calendar_default_view: this.business.calendar_default_view,
                    calendar_caregiver_filter: this.business.calendar_caregiver_filter,
                    auto_confirm: this.business.auto_confirm,
                }),
            }
        },

        methods: {
            update() {
                this.form.put('/business/settings/' + this.business.id);
            }
        }
    }
</script>