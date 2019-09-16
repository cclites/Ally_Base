<template>
    <div>
        <form v-show="showForm" @submit.prevent="submitForm()" @keydown="form.clearError($event.target.name)">
            <b-row>
                <b-col lg="6">
                    <b-form-group label="Select a Client" label-for="client_id" label-class="required" required>
                        <b-form-select
                                id="client_id"
                                name="client_id"
                                v-model="clientId"
                                required
                        >
                            <option value="">--Select a Client--</option>
                            <option v-for="item in localClients" :value="item.id" :key="item.id">{{ item.nameLastFirst }}</option>
                        </b-form-select>
                        <input-help :form="form" field="client_id" text="" />
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="6">
                    <h5>Find caregivers who...</h5>

                    <legend class="col-form-legend pt-0">Are available on (matches availability and existing schedules)</legend>
                    <b-form-group label="Start Date" label-for="startDate">
                        <date-picker v-model="startDate" />
                        <input-help :form="form" field="starts_at" text="" />
                    </b-form-group>
                    <b-row>
                        <b-col lg="6">
                            <b-form-group label="Start Time" label-for="startTime">
                                <time-picker
                                        id="startTime"
                                        name="startTime"
                                        v-model="startTime"
                                />
                                <input-help :form="form" field="starts_at" text="" />
                            </b-form-group>
                        </b-col>
                        <b-col lg="6">
                            <b-form-group label="End Time" label-for="endTime">
                                <time-picker
                                        id="endTime"
                                        name="endTime"
                                        v-model="endTime"
                                />
                                <input-help :form="form" field="duration" text="" />
                            </b-form-group>
                        </b-col>
                    </b-row>
                    <b-form-group label="Are also available on the following days of the week:">
                        <label class="custom-control custom-checkbox" v-for="day in daysOfWeek" :key="day">
                            <input type="checkbox" class="custom-control-input" v-model="days" :value="day">
                            <span class="custom-control-indicator"></span>
                            <span class="custom-control-description">{{ day | capitalize }}</span>
                        </label>
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <div class="form-check">
                        <label class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" v-model="activities" :true-value="1" :false-value="null">
                            <span class="custom-control-indicator"></span>
                            <span class="custom-control-description">Caregiver's skills match ALL of the client's ADL requirements (service needs)</span>
                        </label>
                    </div>
                    <div class="form-check">
                        <label class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" v-model="activities" :true-value=".01" :false-value="null">
                            <span class="custom-control-indicator"></span>
                            <span class="custom-control-description">Caregiver's skills match at least 1 of the client's ADL requirements</span>
                        </label>
                    </div>
                    <!--<div class="form-check">-->
                        <!--<label class="custom-control custom-checkbox">-->
                            <!--<input type="checkbox" class="custom-control-input" v-model="preferences" :value="1">-->
                            <!--<span class="custom-control-indicator"></span>-->
                            <!--<span class="custom-control-description">Caregiver matches the client's preferences</span>-->
                        <!--</label>-->
                    <!--</div>-->
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
                    <div>
                        <b-form-group label="Caregiver Gender" label-for="gender">
                            <b-form-select id="gender"
                                            v-model="gender"
                            >
                                <option value="">No Preference</option>
                                <option value="client">Match Client Preference</option>
                                <option value="M">Male</option>
                                <option value="F">Female</option>
                            </b-form-select>
                            <input-help :form="form" field="matches_gender" text="" />
                        </b-form-group>
                        <b-form-group label="Caregiver Certification" label-for="certification">
                            <b-form-select id="certification"
                                            v-model="certification"
                            >
                                <option value="">No Preference</option>
                                <option value="client">Match Client Preference</option>
                                <option value="CNA">CNA</option>
                                <option value="HHA">HHA</option>
                                <option value="RN">RN</option>
                                <option value="LPN">LPN</option>
                            </b-form-select>
                            <input-help :form="form" field="matches_certification" text="" />
                        </b-form-group>
                        <b-form-group label="Spoken Language" label-for="language">
                            <b-form-select id="language"
                                            v-model="language"
                            >
                                <option value="">No Preference</option>
                                <option value="client">Match Client Preference</option>
                                <option v-for="lang in languages.getOptions()" :value="lang.value">{{ lang.text }}</option>
                            </b-form-select>
                            <input-help :form="form" field="matches_language" text="" />
                        </b-form-group>
                        <b-form-group label="Smoking" label-for="smoking">
                            <b-form-select id="smoking" v-model="smoking">
                                <option value="client">Match Client Preference</option>
                                <option :value="1">Okay with smoking</option>
                                <option :value="0">Not okay with smoking</option>
                            </b-form-select>
                            <input-help :form="form" field="smoking" text="" />
                        </b-form-group>
                        <b-form-group label="Pets">
                            <b-form-select v-model="pets">
                                <option value="">No Preference</option>
                                <option value="client">Match Client Preference</option>
                                <option value="select">Select Pet Types</option>
                            </b-form-select>
                            <input-help :form="form" field="pets" text="" />
                            <div v-if="pets == 'select'">
                                <b-form-checkbox v-model="pets_dogs" value="1" unchecked-value="0">Dogs</b-form-checkbox>
                                <b-form-checkbox v-model="pets_cats" value="1" unchecked-value="0">Cats</b-form-checkbox>
                                <b-form-checkbox v-model="pets_birds" value="1" unchecked-value="0">Birds</b-form-checkbox>
                            </div>
                        </b-form-group>

                        <b-form-group label="Caregiver Ethnicity">
                            <b-form-select v-model="ethnicity">
                                <option value="">No Preference</option>
                                <option value="client">Match Client Preference</option>
                                <option value="select">Select Specific Ethnicities</option>
                            </b-form-select>
                            <input-help :form="form" field="ethnicity" />
                            <div v-if="ethnicity == 'select'">
                                <b-form-checkbox v-for="item in ethnicityOptions"
                                    :key="item.value"
                                    v-model="ethnicities"
                                    :value="item.value"
                                    unchecked-value="null"
                                >
                                    {{ item.text }}
                                </b-form-checkbox>
                                <input-help :form="form" field="ethnicities" />
                            </div>
                        </b-form-group>
                    </div>

                    <!--<div class="form-check">-->
                        <!--<label class="custom-control custom-checkbox">-->
                            <!--<input type="checkbox" class="custom-control-input" v-model="ratingEnabled">-->
                            <!--<span class="custom-control-indicator"></span>-->
                            <!--<span class="custom-control-description">Caregiver has a rating of at least <input type="number" step="1" :disabled="!ratingEnabled" v-model="rating" class="form-control-sm col-2"/> stars</span>-->
                        <!--</label>-->
                    <!--</div>-->
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
                    <h4>
                        Matches: {{ matches.length }}
                        <b-btn v-if="matches.length > 0" variant="success" class="ml-3" @click="SmsMatches()">Text Message All Matching Caregivers</b-btn>
                    </h4>
                </b-col>
                <b-col lg="6" class="text-right">
                    <b-btn variant="info" @click="showForm = true" v-show="!showForm">Modify Search</b-btn>
                </b-col>
            </b-row>
        </div>

        <loading-card v-show="loading"></loading-card>
        <div class="table-responsive">
            <b-table bordered striped hover show-empty
                     :items="matches"
                     :fields="fields"
                     :sort-by.sync="sortBy"
                     :sort-desc.sync="sortDesc"
                     v-show="!loading"
            >
                <template slot="distance" scope="row">
                    {{ row.item.distance == 'Unavailable' ? row.item.distance : convertToMiles(row.item.distance) }}
                </template>
                <template slot="actions" scope="row">
                    <slot :item="row.item">
                        <b-button :href="'/business/caregivers/' + row.item.id" size="sm">View Caregiver</b-button>
                        <b-button :href="'/business/clients/' + clientId" size="sm">View Client</b-button>
                    </slot>
                </template>
            </b-table>
        </div>
    </div>
</template>

<script>
    import FormatsNumbers from "../../mixins/FormatsNumbers";
    import FormatsDistance from "../../mixins/FormatsDistance";
    import Languages from "../../classes/Languages";
    import Constants from '../../mixins/Constants';

    export default {
        mixins: [Constants, FormatsNumbers, FormatsDistance],

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
                clientId: "",
                activities: null,
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
                gender: 'client',
                certification: 'client',
                language: 'client',
                days: [],
                smoking: 'client',
                pets: 'client',
                pets_dogs: 0,
                pets_cats: 0,
                pets_birds: 0,
                ethnicity: '',
                ethnicities: [],

                languages: new Languages(),
                daysOfWeek: ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'],

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
                        formatter: val => numeral(val).format('0%'),
                    },
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
            this.setDataFromSchedule();
        },

        methods: {
            SmsMatches() {
                let ids = _.map(this.matches, 'id');

                let form = new Form({ids});
                form.put(`/business/communication/text-caregivers`)
                    .then(response => {
                        console.log('response: ', response);
                    })
                    .catch(e => {
                        console.log('error: ', e);
                    })
            },

            async loadClients() {
                const response = await axios.get('/business/clients?json=1');
                this.localClients = response.data;
            },

            async getMatches(callback) {
                this.form = this.makeForm();
                this.loading = true;
                this.matches = [];

                this.form.post(`/business/care-match/client-matches/${this.clientId}`)
                    .then( ({ data }) => {
                        this.matches = data;
                        if (callback) {
                            callback();
                        }
                    })
                    .catch(e => {})
                    .finally(() => {
                        this.loading = false;
                    })
            },

            setDataFromSchedule()
            {
                if (!this.schedule) return;

                let startsAt = moment(this.schedule.starts_at, 'YYYY-MM-DD HH:mm:ss');
                console.log(startsAt);

                this.clientId = this.schedule.client_id;
                this.startDate = startsAt.format('MM/DD/YYYY');
                this.startTime = startsAt.format('HH:mm');
                this.endTime = moment(startsAt).add(this.schedule.duration, 'minutes').format('HH:mm');
            },

            makeForm() {
                return new Form({
                    starts_at: this.getStartsAt(),
                    duration: this.getDuration(),
                    matches_activities: this.activities,
                    // matches_preferences: this.preferences,
                    matches_gender: this.gender,
                    matches_certification: this.certification,
                    matches_language: this.language,
                    matches_days: this.days,
                    matches_existing_assignments: this.existing,
                    exclude_overtime: this.excludesOvertime,
                    radius: this.radiusEnabled ? this.radius : null,
                    rating: this.ratingEnabled ? this.rating : null,
                    smoking: this.smoking,
                    pets_dogs: this.pets_dogs,
                    pets_cats: this.pets_cats,
                    pets_birds: this.pets_birds,
                    pets: this.pets,
                    ethnicity: this.ethnicity,
                    ethnicities: this.ethnicities,
                })
            },

            async submitForm() {
                await this.getMatches();
            },

            getDuration() {
                if (this.endTime && this.startTime) {
                    if (this.startTime === this.endTime) {
                        return 1440; // have 12:00am to 12:00am = 24 hours
                    }
                    let start = moment('09/21/2018 ' + this.startTime, 'MM/DD/YYYY HH:mm');
                    let end = moment('09/21/2018 ' + this.endTime, 'MM/DD/YYYY HH:mm');
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

            getStartsAt() {
                return this.startDate && this.startTime ? `${this.startDate} ${this.startTime}` : null;
            }
        },

        watch: {
            clientId() {
                this.matches = [];
            },
            schedule: {
                deep: true,
                handler() { this.setDataFromSchedule(); }
            },
            clients(val) {
                this.localClients = val;
            },
        }
    }
</script>
