<template>
    <div>
        <form v-show="showForm" @submit.prevent="submitForm()" @keydown="form.clearError($event.target.name)">
            <b-row>
                <b-col>
                    <b-form-group label="Client" label-for="client_id" required>
                        <b-form-select
                                id="client_id"
                                name="client_id"
                                v-model="clientId"
                                required
                        >
                            <option value="">--Select a Client--</option>
                            <option v-for="item in localClients" :value="item.id" :key="item.id">{{ item.name }}</option>
                        </b-form-select>
                        <input-help :form="form" field="client_id" text="Select the client for this schedule." />
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="6">
                    <b-form-group label="Start Date" label-for="startDate">
                        <date-picker v-model="startDate" />
                        <input-help :form="form" field="starts_at" text="" />
                    </b-form-group>
                    <b-form-group label="Start Time" label-for="startTime">
                        <time-picker
                                id="startTime"
                                name="startTime"
                                v-model="startTime"
                        />
                        <input-help :form="form" field="starts_at" text="" />
                    </b-form-group>
                    <b-form-group label="End Time" label-for="endTime">
                        <time-picker
                                id="endTime"
                                name="endTime"
                                v-model="endTime"
                        />
                        <input-help :form="form" field="duration" text="" />
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <h5>Criteria</h5>
                    <div class="form-check">
                        <label class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" v-model="activities" :value="1">
                            <span class="custom-control-indicator"></span>
                            <span class="custom-control-description">Caregiver's skills match the client's ADL requirements</span>
                        </label>
                    </div>
                    <div class="form-check">
                        <label class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" v-model="preferences" :value="1">
                            <span class="custom-control-indicator"></span>
                            <span class="custom-control-description">Caregiver matches the client's preferences</span>
                        </label>
                    </div>
                    <div class="form-check">
                        <label class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" v-model="excludesOvertime" :value="1">
                            <span class="custom-control-indicator"></span>
                            <span class="custom-control-description">Caregiver will not hit overtime working this shift</span>
                        </label>
                    </div>
                    <div class="form-check">
                        <label class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" v-model="existing" :value="1">
                            <span class="custom-control-indicator"></span>
                            <span class="custom-control-description">Caregiver has worked for this client previously</span>
                        </label>
                    </div>
                    <div class="form-check">
                        <label class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" v-model="radiusEnabled">
                            <span class="custom-control-indicator"></span>
                            <span class="custom-control-description">Located within a <input type="number" step="1" v-model="radius" :disabled="!radiusEnabled" class="form-control-sm col-2"/> mile radius of service address</span>
                        </label>
                    </div>
                    <div class="form-check">
                        <label class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" v-model="ratingEnabled">
                            <span class="custom-control-indicator"></span>
                            <span class="custom-control-description">Caregiver has a rating of at least <input type="number" step="1" :disabled="!ratingEnabled" v-model="rating" class="form-control-sm col-2"/> stars</span>
                        </label>
                    </div>
                </b-col>
            </b-row>
            <b-row>
                <b-col class="text-right">
                    <b-btn variant="info" type="submit">Find Caregivers</b-btn>
                </b-col>
            </b-row>
        </form>

        <div>
            <b-row>
                <b-col lg="6">
                    <h4>Matches</h4>
                </b-col>
                <b-col lg="6" class="text-right">
                    <b-btn variant="info" @click="showForm = true" v-show="!showForm">Modify Search</b-btn>
                </b-col>
            </b-row>
        </div>

        <loading-card v-show="loading"></loading-card>
        <b-table bordered striped hover show-empty
                 :items="matches"
                 :fields="fields"
                 :sort-by.sync="sortBy"
                 :sort-desc.sync="sortDesc"
                 v-show="!loading"
        >
            <template slot="rating" scope="row">

            </template>
            <template slot="actions" scope="row">
                <b-button :href="'/business/caregivers/' + row.item.id" size="sm">View Caregiver</b-button>
                <b-button :href="'/business/clients/' + form.client_id" size="sm">View Client</b-button>
            </template>
        </b-table>
    </div>
</template>

<script>
    export default {
        props: {
            clients: Array,
            schedule: {
                type: Object,
                default: () => {}
            },
        },

        data() {
            return {
                showForm: true,
                form: new Form({}),
                localClients: this.clients || [],
                clientId: null,
                activities: false,
                preferences: false,
                existing: false,
                excludesOvertime: false,
                startDate: null,
                startTime: null,
                endTime: null,
                radius: 10,
                rating: 3,
                radiusEnabled: false,
                ratingEnabled: false,

                matches: [],
                fields: [
                    {
                        key: 'nameLastFirst',
                        label: 'Name',
                        sortable: true,
                    },
                    {
                        key: 'email',
                        sortable: true,
                    },
                    {
                        key: 'distance',
                        sortable: true,
                    },
                    {
                        key: 'activity_match',
                        label: 'ADL Match',
                        sortable: true,
                    },
                    'rating',
                    'actions'
                ],
                sortBy: 'distance',
                sortDesc: false,
                loading: false,
            }
        },

        mounted() {
            if (!this.clients) {
                this.loadClients();
            }
        },

        methods: {
            async loadClients() {
                const response = await axios.get('/business/clients?json=1');
                this.localClients = response.data;
            },

            async getMatches(callback) {
                this.form = this.makeForm();
                this.loading = true;
                const response = await this.form.post(`/business/care-match/client-matches/${this.clientId}`);
                this.matches = response.data;
                this.loading = false;
                if (callback) {
                    callback();
                }
            },

            makeForm() {
                return new Form({
                    starts_at: `${this.startDate} ${this.startTime}`,
                    duration: this.getDuration(),
                    matches_activities: this.activities,
                    matches_preferences: this.preferences,
                    matches_existing_assignments: this.existing,
                    exclude_overtime: this.excludesOvertime,
                    radius: this.radiusEnabled ? this.radius : null,
                    rating: this.ratingEnabled ? this.rating : null,
                })
            },

            async submitForm() {
                await this.getMatches(() => this.showForm = false);
            },

            getDuration() {
                if (this.endTime && this.startTime) {
                    if (this.startTime === this.endTime) {
                        return 1440; // have 12:00am to 12:00am = 24 hours
                    }
                    let start = moment(this.startDate + ' ' + this.startTime, 'MM/DD/YYYY HH:mm');
                    let end = moment(this.startDate + ' ' + this.endTime, 'MM/DD/YYYY HH:mm');
                    console.log(start, end);
                    if (start && end) {
                        if (end.isBefore(start)) {
                            end = end.add(1, 'days');
                        }
                        let diff = end.diff(start, 'minutes');
                        if (diff) {
                            return parseInt(diff);
                        }
                    }
                }
                return null;
            },
        },
    }
</script>
