import FormatDates from './FormatsDates';

export default {
    mixins: [ FormatDates ],

    data () {

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
    },

    methods: {
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
