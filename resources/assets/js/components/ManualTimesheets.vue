<template>
    <b-card header="Manual Timesheet"
            border-variant="info"
            header-bg-variant="info"
            header-text-variant="white">
            
        <!-- CLIENT DROPDOWNS -->
        <b-row>
            <b-col lg="6" v-show="! isCaregiver">
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
                        <option v-for="item in weekRanges" :value="item" :key="item.id">{{ item.display }}</option>
                    </b-form-select>
                    <input-help :form="form" field="week" text=""></input-help>
                </b-form-group>
            </b-col>
        </b-row>
        <!-- /end WEEK DROPDOWN -->
            
        <!-- SHIFTS TABLE -->
        <div class="table-responsive">
            <input-help :form="form" field="shifts" text=""></input-help>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th scope="col"></th>
                        <th scope="col"></th>
                        <th scope="col"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="shift in shifts" :key="shift.date">
                        <th>{{ dow(shift.date) }}</th>
                        <td>
                            {{ shiftDisplayTime(shift) }}
                        </td>
                        <td>
                            <b-button variant="info" size="xs" @click="editShift(shift)">
                                <i class="fa fa-edit"></i>
                            </b-button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <!-- /end SHIFTS TABLE -->

        <b-row class="mt-3">
            <b-col md="12">
                <b-button variant="success" type="button" @click="submit()">Submit Timesheet</b-button>
            </b-col>
        </b-row>

        <b-modal :title="modalTitle" v-model="showModal" size="lg">
            <b-container fluid>
                <b-row>
                    <b-col md="12">

                        <!-- start_time -->
                        <b-form-group label="Clocked In" label-for="start_time">
                            <time-picker v-model="shiftForm.start_time" placeholder="HH:MM"></time-picker>
                            <input-help :form="shiftForm" field="start_time" text=""></input-help>
                        </b-form-group>

                        <!-- end_time -->
                        <b-form-group label="Clocked Out" label-for="end_time">
                            <time-picker v-model="shiftForm.end_time" placeholder="HH:MM"></time-picker>
                            <input-help :form="shiftForm" field="end_time" text=""></input-help>
                        </b-form-group>

                        <!-- activities -->
                        <b-form-group label="Activities Performed Out" label-for="">
                            <input-help :form="shiftForm" field="activities" text=""></input-help>
                            <div class="form-check">
                                <label class="custom-control custom-checkbox" v-for="activity in activities" :key="activity.id" style="clear: left; float: left;">
                                    <input type="checkbox" class="custom-control-input" v-model="shiftForm.activities" :value="activity.id">
                                    <span class="custom-control-indicator"></span>
                                    <span class="custom-control-description">{{ activity.code }} - {{ activity.name }}</span>
                                </label>
                            </div>
                        </b-form-group>

                        <!-- mileage -->
                        <b-form-group label="Mileage" label-for="mileage">
                            <b-form-input
                                    id="mileage"
                                    name="mileage"
                                    type="number"
                                    v-model="shiftForm.mileage"
                                    step="any"
                                    min="0"
                                    max="1000"
                            />
                            <input-help :form="shiftForm" field="mileage" text=""></input-help>
                        </b-form-group>
                        
                        <!-- other_expenses -->
                        <b-form-group label="Other Expenses" label-for="other_expenses">
                            <b-form-input
                                    id="other_expenses"
                                    name="other_expenses"
                                    type="number"
                                    v-model="shiftForm.other_expenses"
                                    step="any"
                                    min="0"
                                    max="1000"
                            />
                            <input-help :form="shiftForm" field="other_expenses" text=""></input-help>
                        </b-form-group>

                        <b-form-group label="Notes" label-for="caregiver_comments">
                            <b-textarea
                                    id="caregiver_comments"
                                    name="caregiver_comments"
                                    :rows="4"
                                    v-model="shiftForm.caregiver_comments"
                            ></b-textarea>
                            <input-help :form="shiftForm" field="caregiver_comments" text=""></input-help>
                        </b-form-group>

                    </b-col>
                </b-row>
            </b-container>

            <div slot="modal-footer">
                <b-button variant="success" type="submit" @click="updateShift()">Save</b-button>
                <b-btn variant="default" @click="showModal = false">Close</b-btn>
            </div>
        </b-modal>
    </b-card>
</template>

<script>
    export default {
        props: {
            'cg': { type: Object, default: {} },
            'caregivers': { type: Array, default: [] },
            'activities': { type: Array, default: [] },
        },

        data() {
            return{
                showModal: false,
                caregiver: {},
                client: {},
                weekRanges: [],
                shiftForm: new Form({}),
                shifts: [],
                form: new Form({
                    client_id: '',
                    caregiver_id: '',
                    shifts: [],
                    week: {},
                }),
            }
        },

        computed: {
            modalTitle() {
                if (! this.shiftForm.date) {
                    return '';
                }

                return this.dow(this.shiftForm.date, true) + ' ' + moment(this.shiftForm.date).format('M/D/YYYY');
            },

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

        methods: {
            editShift(shift) {
                this.shiftForm = new Form(shift);
                this.showModal = true;
            },

            updateShift() {
                console.log(this.shiftForm.data());

                this.shiftForm.clearError();
                if (!this.isValidShift(this.shiftForm)) {
                    return;
                }

                var index = this.shifts.findIndex(x => x.date == this.shiftForm.date);
                this.shifts[index] = this.shiftForm.data();

                this.showModal = false;
            },

            submit() {
                // submit only the shifts filled out  
                this.form.shifts = this.shifts.filter(x => x.start_time != '');

                this.form.submit('post', '/manual-timesheet')
                    .then( ({ data }) => {
                        console.log(data);
                        this.shiftForm = new Form({});
                        this.shifts = [];
                        this.form.week = {};
                        this.form.client_id = '';
                        this.form.shifts = [];
                    })
                    .catch(e => {
                        console.log('submit timesheet error:');
                        console.log(e);
                    });
            },

            dow(date, full = false) {
                return moment(date).format(full ? 'dddd' : 'ddd');
            },

            shiftDisplayTime(shift) {
                if (shift.start_time == '' || shift.end_time == '') {
                    return '-';
                }

                return `${shift.start_time} - ${shift.end_time}`;
            },

            generateShiftsForWeek(week) {
                let shifts = [];
                week.days.forEach( (date) => {
                    shifts.push({
                        date: date,
                        start_time: '',
                        end_time: '',
                        mileage: 0,
                        other_expenses: 0,
                        caregiver_rate: this.defaultRate || 0,
                        provider_fee: this.defaultFee || 0,
                        caregiver_comments: '',
                        activities: [],
                    });
                });
                return shifts;
            },

            isValidShift(data) {
                if (!this.validDate(data.start_time)) {
                    this.shiftForm.addError('start_time', 'Clock in time is required');
                }
                
                if (!this.validDate(data.end_time)) {
                    this.shiftForm.addError('end_time', 'Clock out time is required');
                }

                if (data.mileage === '' || isNaN(data.mileage)) {
                    this.shiftForm.addError('mileage', 'Invalid entry');
                }
                
                if (isNaN(data.other_expenses)) {
                    this.shiftForm.addError('other_expenses', 'Invalid entry');
                }

                if (! data.activities || data.activities.length == 0) {
                    this.shiftForm.addError('activities', 'You must select at least one activity');
                }
                
                // if (isNaN(data.caregiver_rate)) {
                //     this.shiftForm.addError('caregiver_rate', 'Invalid');
                // }

                // if (isNaN(data.provider_fee)) {
                //     this.shiftForm.addError('provider_fee', 'Invalid');
                // }
                
                return !this.shiftForm.hasError();
            },

            validDate(val) {
                if (!val || val == '') return false;
                return moment(val, 'mm/dd/yyyy').isValid();
            },
            
            validTime() {
                if (!val || val == '') return false;
                return moment(this.value, 'hh:mm').isValid();
            },

            generateWeeks() {
                let weeks = [];
                var start = null;
                var end = null;

                for (var i = 0; i < 4; i++) {
                    if (i > 0) {
                        start = moment().subtract(i * 7, 'days').startOf('week');
                        end = moment().subtract(i * 7, 'days').endOf('week');
                    } else {
                        start = moment().startOf('week');
                        end = moment().endOf('week');
                    }

                    let w = {
                       id: i,
                       display: start.format('M/D/YYYY') + ' - ' + end.format('M/D/YYYY'),
                       days: this.getDatesInRange(start, end),
                    };
                    weeks.push(w);
                }

                return weeks;
            },

            getDatesInRange(start, end) {
                let days = [];
                for (var i = 6; i >= 0; i--) {
                    days.push(moment(end).subtract(i, 'days').format('YYYY-MM-DD'));                    
                }
                return days;
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
                
                this.shifts = this.generateShiftsForWeek(this.form.week);
            },

            'form.week': function(newVal, oldVal) {
                this.shifts = this.generateShiftsForWeek(this.form.week);
            }
        },

        mounted() {
            if (this.cg.id) {
                this.caregiver = this.cg;
                this.form.caregiver_id = this.caregiver.id;
            }

            this.weekRanges = this.generateWeeks();
            this.form.week = this.weekRanges[0];
        },
    }
</script>