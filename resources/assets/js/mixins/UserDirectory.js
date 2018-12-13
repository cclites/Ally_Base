export default {
    data() {
        return {
            totalRows: 0,
            perPage: 15,
            currentPage: 1,
            columnsModal: false,
            filters: {
                start_date: '',
                end_date: '',
                active: null,
            },
        };
    },

    computed: {
        items() {
            const {start_date, end_date, active} = this.filters;
            let items = this.data;

            if(start_date && end_date) {
                items = items.filter(({user}) => moment(user.created_at).isBetween(start_date, end_date));
            }

            if(typeof active == 'boolean') {
                items = items.filter(client => client.active == active);
            }

            return items;
        },

        fields() {
            let fields = Object.keys(this.columns).filter(key => this.columns[key].shouldShow);
            fields = fields.map(col => ({
                    sortable: true,
                    ...this.columns[col],
            }));

            return fields;
        },

        downloadableUrl() {
            // Convert the selected columns to a valid query string
            const columnsToRemove = Object.keys(this.columns).map(key => {
                const value = Number(this.columns[key].shouldShow);
                return `${key}=${value}`;
            });

            // convert the applied filters to a valid query string
            let filtersToApply = Object.keys(this.filters).map(key => {
                let value = this.filters[key];
                value = typeof value  == 'boolean' ? Number(value) : value;

                return (value === null || value === '') ? undefined : `${key}=${value}`;
            });
    
            filtersToApply = filtersToApply.filter(value => typeof value !== 'undefined'); // Remove all non applied filters

            return `/business/reports/${this.directoryType}-directory/download?export=1&${columnsToRemove.join('&')}&${filtersToApply.join('&')}`;
        },
    },

    methods: {
        formatDate(date) {
            return moment(date).format('MM-DD-YYYY');
        },

        onFiltered(filteredItems) {
            // Trigger pagination to update the number of buttons/pages due to filtering
            this.totalRows = filteredItems.length;
            this.currentPage = 1;
        },

        printTable() {
            $('#table').print();
        },
    }
}