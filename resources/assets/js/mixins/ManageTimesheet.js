import FormatDates from './FormatsDates';
import RateFactory from "../classes/RateFactory";

export default {
    mixins: [ FormatDates ],

    props: {
        'activities': { type: Array, default() {
                return [];
            }
        },
        'caregivers': {
            type: Array,
            default() {
                return [];
            }
        },
        'timesheet': {
            type: Object,
            default() {
                return {};
            }
        },
    },

    data () {
        return {
            caregiver: {},
            client: {},
            weekRanges: [],
            week: {},
            shifts: [],
            services: [],
            clientRates: [],
            form: new Form({}),
            selectedEntry: {},
            selectedIndex: null,
            sheet: {},
            showEntryModal: false,
        }
    },

    computed: {
        emptyShift() {
            return {
                checked_in_time: '',
                checked_out_time: '',
                mileage: '',
                other_expenses: '',
                caregiver_rate: 0.00,
                client_rate: 0.00,
                caregiver_comments: '',
                activities: [],
            };
        },

        emptyTimesheet() {
            return {
                approved_at: null,
                business: {},
                business_id: '',
                caregiver: {},
                caregiver_id: '',
                client: {},
                client_id: '',
                created_at: '',
                creator_id: '',
                denied_at: null,
                entries: [],
                id: null,
                updated_at: '',
            };
        },

        isApproved() {
            return !!(this.form.id && this.form.approved_at);
        },

        isDenied() {
            return !!(this.form.id && this.form.denied_at);
        },

        hasClients() {
            return this.caregiver.clients && this.caregiver.clients.length > 0;
        },

        defaultService() {
            return this.services.find(item => item.default === true) || {};
        },

        defaultRate() {
            let effectiveDate = this.week.days ? this.week.days[0] : moment().format('YYYY-MM-DD');
            let serviceId = this.defaultService ? this.defaultService.id : null;

            return RateFactory.findMatchingRate(this.clientRates, effectiveDate, serviceId, null, this.timesheet.caregiver_id, false);
        },

        canEdit() {
            return this.form.client_id ? true : false;
        },

        mode() {
            if (this.sheet.id) {
                // has a timesheet
                if (this.sheet.exception_count > 0) {
                    // reviewing caregiver submitted timesheet
                    return 'review';
                } else {
                    // editing office user timesheet
                    return 'edit';
                }
            } else {
                // creating office user timesheet
                return 'create';
            }
        },

        isReviewing() { 
            return this.mode == 'review';
        },

        isEditing() {
            return this.mode == 'edit';
        },

        isCreating() {
            return this.mode == 'create';
        }, 

        isLocked() {
            return this.isApproved || this.isDenied;
        },
    },

    methods: {
        editEntry(index) {
            this.selectedIndex = index;
            this.selectedEntry = this.form.entries[index];

            // set default check in time for day of the week
            if (! this.selectedEntry.checked_in_time) {
                this.selectedEntry.checked_in_time = moment(this.week.days[index], 'YYYY-MM-DD');
            }

            this.showEntryModal = true;
        },

        updateEntry(entry) {
            this.form.entries.splice(this.selectedIndex, 1, entry);
            this.shifts = this.form.entries;
            this.selectedEntry = null;
        },

        dow(date, full = false) {
            return moment(date).format(full ? 'dddd' : 'ddd');
        },

        generateEntriesForWeek(week, entriesForDate, caregiverRate, clientRate) {
            let entries = [];
            week.days.forEach( (date) => {
                var index = entriesForDate.findIndex(item => { return item.date == date });
                if (index > -1) {
                    entries.push(entriesForDate[index].entry);
                } else {
                    entries.push({
                        ...this.emptyShift,
                        caregiver_rate: caregiverRate || 0.00,
                        client_rate: clientRate || 0.00,
                    });
                }
            });
            return entries;
        },

        generateWeeks(date) {
            let weeks = [];
            date = date ? moment.utc(date) : moment();

            if (moment().diff(date, 'days') > 7) {
                date.add(7, 'days');
            }

            for (var i = 0; i < 6; i++) {
                if (i > 0) {
                    date =  date.subtract(7, 'days');
                }

                let w = this.getWeekObject(i, date);
                weeks.push(w);
            }

            return weeks;
        },

        getWeekObject(id, dateObj) {
            let start = moment(dateObj).startOf('isoweek');
            let end = moment(dateObj).endOf('isoweek');

            console.log(start, end);

            return {
                id: id,
                display: start.format('M/D/YYYY') + ' - ' + end.format('M/D/YYYY'),
                days: this.getDatesInRange(start, end),
            };
        },

        selectWeek(dateObj) {
            let start = moment(dateObj).startOf('isoweek');
            let end = moment(dateObj).endOf('isoweek');
            this.week = this.weekRanges.find(item => {
                return item.display === start.format('M/D/YYYY') + ' - ' + end.format('M/D/YYYY');
            });
        },

        getDatesInRange(start, end) {
            let days = [];
            for (var i = 6; i >= 0; i--) {
                days.push(moment(end).subtract(i, 'days').format('YYYY-MM-DD'));                    
            }
            return days;
        },

        formatEntryDisplay(entry) {
            if (!entry) {
                return '-'; 
            }

            if (entry.checked_in_time && entry.checked_out_time) {
                return this.formatTimeFromUTC(entry.checked_in_time) + ' - ' + this.formatTimeFromUTC(entry.checked_out_time);
            } else {
                return '-';
            }
        },

        prepareTimesheet() {
            if (this.timesheet.id) {
                let entry = this.timesheet.entries[0];
                this.weekRanges = this.generateWeeks(entry.checked_in_time);
                this.selectWeek(moment.utc(entry.checked_in_time));
                this.sheet = this.timesheet;
                this.form = new Form(this.sheet);
            } else {
                this.weekRanges = this.generateWeeks();
                this.week = this.weekRanges[0];
                this.form = new Form(this.emptyTimesheet);
            }
        },

        loadTimesheet(timesheet) {
            this.sheet = timesheet;

            let entriesForDates = [];
            if (this.sheet.id) {
                entriesForDates = this.sheet.entries.map(item => {
                    return {
                        date: moment.utc(item.checked_in_time).local().format('YYYY-MM-DD'),
                        entry: item,
                    }
                });
            }
            this.shifts = this.generateEntriesForWeek(this.week, entriesForDates, this.defaultRate.caregiver_rate, this.defaultRate.client_rate);
            this.form.entries = this.shifts;
        },

        async fetchServices() {
            let response = await axios.get('/business/services?json=1');
            if (Array.isArray(response.data)) {
                this.services = response.data;
            } else {
                this.services = [];
            }
        },

        async loadClientRates(clientId) {
            if (clientId) {
                const response = await axios.get(`/business/clients/${clientId}/rates`);
                this.clientRates = response.data;
            }
        },
    },

    mounted() {
        this.fetchServices();
        this.loadClientRates();
        this.prepareTimesheet();
    },

    watch: {
        /**
         * sets client dropdown to only selected caregivers clients
         * and resets the shift form.
         */
        'form.caregiver_id': function(newVal, oldVal) {
            var results = this.caregivers.filter(function(c) {
                return c.id == newVal;
            });

            if (results && results.length == 1) {
                this.caregiver = results[0];
                // only reset client_id if doesn't exist in dropdown
                if (this.caregiver.clients.findIndex(item => item.id == this.form.client_id) == -1) {
                    this.form.client_id = '';
                }
            } else {
                this.caregiver = {};
                this.form.client_id = '';
            }

        },

        /**
         * sets current selected client so rates/fees can be loaded
         * and resets the shift form.
         */
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

            this.loadTimesheet(this.sheet);
        },

        'week': function(newVal, oldVal) {
            this.loadTimesheet(this.sheet);
        },

        /**
         * Clear entry form when modal closes.
         */
        showEntryModal(val) {
            if (val == false) {
                this.selectedEntry = {};
            }
        },
    },
}
