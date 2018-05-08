<template>
    <b-card header="Manual Timesheet"
            border-variant="info"
            header-bg-variant="info"
            header-text-variant="white">
            
        <!-- CLIENT DROPDOWNS -->
        <b-row>
            <b-col lg="6">
                <b-form-group label="Caregiver" label-for="caregiver_id">
                    <b-form-select
                            id="caregiver_id"
                            name="caregiver_id"
                            v-model="form.caregiver_id"
                    >
                        <option value="">-- Select Caregiver --</option>
                        <option v-for="item in caregivers" :value="item.id" :key="item.id">{{ item.name }}</option>
                    </b-form-select>
                    <input-help :form="form" field="caregiver_id" text=""></input-help>
                </b-form-group>
            </b-col>
            <b-col lg="6">
                <b-form-group label="Client" label-for="client_id">
                    <b-form-select
                            :disabled="!hasClients"
                            id="client_id"
                            name="client_id"
                            v-model="form.client_id"
                    >
                        <option value="">-- Select Client --</option>
                        <option v-for="item in caregiver.clients" :value="item.id" :key="item.id">{{ item.name }}</option>
                    </b-form-select>
                    <input-help :form="form" field="client_id" text=""></input-help>
                </b-form-group>
            </b-col>
        </b-row>
        <!-- /end CLIENT DROPDOWNS -->

        <!-- WEEK DROPDOWN -->
        <b-row>
            <b-col lg="6">
                <b-form-group label="Week" label-for="week">
                    <b-form-select
                            id="week"
                            name="week"
                            v-model="form.week"
                    >
                        <option value="">-- Select Week --</option>
                        <option v-for="item in weekRanges" :value="item" :key="item">{{ item }}</option>
                    </b-form-select>
                    <input-help :form="form" field="week" text=""></input-help>
                </b-form-group>
            </b-col>
        </b-row>
        <!-- /end WEEK DROPDOWN -->
            
        <!-- SHIFTS TABLE -->
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th scope="col"></th>
                        <th scope="col">Sun</th>
                        <th scope="col">Mon</th>
                        <th scope="col">Tues</th>
                        <th scope="col">Wed</th>
                        <th scope="col">Thur</th>
                        <th scope="col">Fri</th>
                        <th scope="col">Sat</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th>Time In</th>
                        <td scope="row"> <!-- start_time -->
                            <time-picker v-model="shiftForm.start_time" placeholder="HH:MM"></time-picker>
                            <input-help :form="shiftForm" field="start_time" text=""></input-help>
                        </td>
                        <td scope="row"> <!-- start_time -->
                            <time-picker v-model="shiftForm.start_time" placeholder="HH:MM"></time-picker>
                            <input-help :form="shiftForm" field="start_time" text=""></input-help>
                        </td>
                        <td scope="row"> <!-- start_time -->
                            <time-picker v-model="shiftForm.start_time" placeholder="HH:MM"></time-picker>
                            <input-help :form="shiftForm" field="start_time" text=""></input-help>
                        </td>
                        <td scope="row"> <!-- start_time -->
                            <time-picker v-model="shiftForm.start_time" placeholder="HH:MM"></time-picker>
                            <input-help :form="shiftForm" field="start_time" text=""></input-help>
                        </td>
                        <td scope="row"> <!-- start_time -->
                            <time-picker v-model="shiftForm.start_time" placeholder="HH:MM"></time-picker>
                            <input-help :form="shiftForm" field="start_time" text=""></input-help>
                        </td>
                        <td scope="row"> <!-- start_time -->
                            <time-picker v-model="shiftForm.start_time" placeholder="HH:MM"></time-picker>
                            <input-help :form="shiftForm" field="start_time" text=""></input-help>
                        </td>
                        <td scope="row"> <!-- start_time -->
                            <time-picker v-model="shiftForm.start_time" placeholder="HH:MM"></time-picker>
                            <input-help :form="shiftForm" field="start_time" text=""></input-help>
                        </td>
                    </tr>
                    <tr>
                        <th>Time Out</th>
                        <td scope="row"> <!-- end_time -->
                            <time-picker v-model="shiftForm.end_time" placeholder="HH:MM"></time-picker>
                            <input-help :form="shiftForm" field="end_time" text=""></input-help>
                        </td>
                        <td scope="row"> <!-- end_time -->
                            <time-picker v-model="shiftForm.end_time" placeholder="HH:MM"></time-picker>
                            <input-help :form="shiftForm" field="end_time" text=""></input-help>
                        </td>
                        <td scope="row"> <!-- end_time -->
                            <time-picker v-model="shiftForm.end_time" placeholder="HH:MM"></time-picker>
                            <input-help :form="shiftForm" field="end_time" text=""></input-help>
                        </td>
                        <td scope="row"> <!-- end_time -->
                            <time-picker v-model="shiftForm.end_time" placeholder="HH:MM"></time-picker>
                            <input-help :form="shiftForm" field="end_time" text=""></input-help>
                        </td>
                        <td scope="row"> <!-- end_time -->
                            <time-picker v-model="shiftForm.end_time" placeholder="HH:MM"></time-picker>
                            <input-help :form="shiftForm" field="end_time" text=""></input-help>
                        </td>
                        <td scope="row"> <!-- end_time -->
                            <time-picker v-model="shiftForm.end_time" placeholder="HH:MM"></time-picker>
                            <input-help :form="shiftForm" field="end_time" text=""></input-help>
                        </td>
                        <td scope="row"> <!-- end_time -->
                            <time-picker v-model="shiftForm.end_time" placeholder="HH:MM"></time-picker>
                            <input-help :form="shiftForm" field="end_time" text=""></input-help>
                        </td>
                    </tr>
<!--                     
                        <th scope="row"> 
                            <time-picker v-model="shiftForm.end_time" placeholder="HH:MM"></time-picker>
                            <input-help :form="shiftForm" field="end_time" text=""></input-help>
                        </th>
                        <th scope="row"> 
                            <b-form-input
                                    id="mileage"
                                    name="mileage"
                                    type="number"
                                    v-model="shiftForm.mileage"
                                    step="any"
                                    min="0"
                                    max="1000"
                            >
                            </b-form-input>
                            <input-help :form="shiftForm" field="mileage" text=""></input-help>
                        </th>
                        <th scope="row"> 
                            <b-form-input
                                    id="other_expenses"
                                    name="other_expenses"
                                    type="number"
                                    v-model="shiftForm.other_expenses"
                                    step="any"
                                    min="0"
                                    max="1000"
                            >
                            </b-form-input>
                            <input-help :form="shiftForm" field="other_expenses" text=""></input-help>
                        </th>
                    </tr> -->

                </tbody>
            </table>
        </div>
        <!-- /end SHIFTS TABLE -->

        <!-- ACTIVITIES / NOTES -->
        <b-row class="mt-3">
            <b-col md="6">
                <label>Global Activities</label>
                <div class="form-check">
                    <label class="custom-control custom-checkbox" v-for="activity in activities" :key="activity.id" style="clear: left; float: left;">
                        <input type="checkbox" class="custom-control-input" v-model="form.activities" :value="activity.id">
                        <span class="custom-control-indicator"></span>
                        <span class="custom-control-description">{{ activity.code }} - {{ activity.name }}</span>
                    </label>
                </div>
            </b-col>
            <b-col md="6">
                <b-form-group label="Global Notes" label-for="caregiver_comments">
                    <b-textarea
                            id="caregiver_comments"
                            name="caregiver_comments"
                            :rows="8"
                            v-model="form.caregiver_comments"
                    ></b-textarea>
                    <input-help :form="form" field="caregiver_comments" text=""></input-help>
                </b-form-group>
            </b-col>
        </b-row>
        <!-- /end ACTIVITIES / NOTES -->

        <b-row class="mt-3 text-right">
            <b-col md="12">
                <b-button variant="success" type="button" @click="submit()">Submit Timesheet</b-button>
            </b-col>
        </b-row>

    </b-card>
</template>

<script>
    export default {
        props: {
            'caregivers': {
                type: Array,
                default: [],
            },
            'activities': {
                type: Array,
                default: [],
            },
        },

        data() {
            return{
                caregiver: {},
                client: {},
                weekRanges: ['4/29/18 - 5/5/18'],
                
                form: new Form({
                    client_id: '',
                    caregiver_id: '',
                    shifts: [],
                    activities: [],
                    caregiver_comments: '',
                    week: '',
                }),
                shiftForm: new Form(this.newShift()),
            }
        },

        mounted() {
            if (this.isCaregiver) {
                this.form.caregiver_id = this.caregiver.id;
            }
        },

        methods: {
            newShift() {
                console.log("default rate;");
                console.log(this.defaultRate);
                return {
                    date: '',
                    start_time: '',
                    end_time: '',
                    mileage: 0,
                    other_expenses: 0,
                    caregiver_rate: this.defaultRate || 0,
                    provider_fee: this.defaultFee || 0,
                }
            },

            submit() {
                this.form.submit('post', '/business/manual-timesheets')
                    .then( ({ data }) => {
                        console.log(data);
                    })
                    .catch(e => {
                        console.log('submit timesheet error:');
                        console.log(e);
                    });
            },

            addShift() {
                this.shiftForm.clearError();
                if (!this.isValidShift(this.shiftForm)) {
                    return;
                }

                this.form.shifts.push(this.shiftForm.data())

                this.shiftForm = new Form(this.newShift());
            },

            removeShift(index) {
                this.form.shifts.splice(index, 1);
            },

            isValidShift(data) {
                if (!this.validDate(data.date)) {
                    this.shiftForm.addError('date', 'Required');
                }
                
                if (!this.validDate(data.start_time)) {
                    this.shiftForm.addError('start_time', 'Required');
                }
                
                if (!this.validDate(data.end_time)) {
                    this.shiftForm.addError('end_time', 'Required');
                }

                if (data.mileage === '' || isNaN(data.mileage)) {
                    this.shiftForm.addError('mileage', 'Invalid');
                }
                
                if (isNaN(data.other_expenses)) {
                    this.shiftForm.addError('other_expenses', 'Invalid');
                }

                if (isNaN(data.caregiver_rate)) {
                    this.shiftForm.addError('caregiver_rate', 'Invalid');
                }

                if (isNaN(data.provider_fee)) {
                    this.shiftForm.addError('provider_fee', 'Invalid');
                }
                
                return !this.shiftForm.hasError();
            },

            validDate(val) {
                if (!val || val == '') return false;
                return moment(val, 'mm/dd/yyyy').isValid();
            },
            
            validTime() {
                if (!val || val == '') return false;
                return moment(this.value, 'hh:mm').isValid();
            }
        },

        computed: {
            isCaregiver() {
                return this.caregiver.id ? true : false;
            },

            hasClients() {
                return this.caregiver.clients && this.caregiver.clients.length > 0;
            },

            defaultRate() {
                return this.client.caregiver_hourly_rate || 0;
            },

            defaultFee() {
                return this.client.provider_hourly_fee || 0;
            },
        },

        watch: {
            // sets client dropdown to only selected caregivers clients
            'form.caregiver_id': function(newVal, oldVal) {
                var results = this.caregivers.filter(function(c) {
                    return c.id == newVal;
                });

                if (results && results.length == 1) {
                    this.caregiver = results[0];
                } else {
                    this.caregiver = {};
                }

                this.form.client_id = '';
            },

            // sets current selected client so rates/fees can be loaded
            // and resets the shift form
            'form.client_id': function(newVal, oldVal) {
                if (this.caregiver.clients) {
                    var results = this.caregiver.clients.filter(function(c) {
                        return c.id == newVal;
                    });

                    if (results && results.length == 1) {
                        this.client = results[0];
                    } else {
                        this.client = {};
                    }
                } else {
                    this.client = {};
                }
                
                this.shiftForm = new Form(this.newShift());
            },
        }
    }
</script>