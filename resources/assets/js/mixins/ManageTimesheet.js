import FormatDates from './FormatsDates';

export default {
    mixins: [ FormatDates ],

    props: {
        'activities': { type: Array, default: [] },
        'caregivers': { type: Array, default: [] },
    },

    data () {
        return {
            caregiver: {},
            client: {},
            weekRanges: [],
            week: {},
            shifts: [],
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
                mileage: 0,
                other_expenses: 0,
                caregiver_rate: 0.00,
                provider_fee: 0.00, 
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
                id: '',
                updated_at: '',
            };
        },

        isApproved() {
            return this.form.id && this.form.approved_at;
        },

        isDenied() {
            return this.form.id && this.form.denied_at;
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

        generateEntriesForWeek(week, entriesForDate, rate, fee) {
            let entries = [];
            week.days.forEach( (date) => {
                var index = entriesForDate.findIndex(item => { return item.date == date });
                if (index > -1) {
                    entries.push(entriesForDate[index].entry);
                } else {
                    entries.push({
                        ...this.emptyShift,
                        caregiver_rate: rate || 0.00,
                        provider_fee: fee || 0.00,
                    });
                }
            });
            return entries;
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
    },
}
