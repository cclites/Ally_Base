<template>
    <b-card>
        <b-row class="mb-2">
            <b-col>
                <b-button-toolbar>

                    <date-picker v-model="search.start" class="mr-2"></date-picker>

                    <date-picker v-model="search.end" class="mr-2"></date-picker>

                    <b-input-group class="w-25 mr-2">
                        <b-form-select v-model="search.caregiver_id">
                            <option value="">All Caregivers</option>
                            <option v-for="caregiver in caregivers" :value="caregiver.id">
                                {{ caregiver.nameLastFirst }}
                            </option>
                        </b-form-select>
                    </b-input-group>

                    <b-button-group size="sm">
                        <b-btn variant="info" @click="fetchData()" :disabled="loading">Search</b-btn>
                        <b-btn @click="reset()">Reset</b-btn>
                    </b-button-group>
                </b-button-toolbar>
            </b-col>
        </b-row>

        <loading-card v-show="loading"></loading-card>
        
        <div v-show="! loading">
            <div class="table-responsive">
                <b-table bordered striped hover show-empty
                        :items="items"
                        :fields="fields"
                        :current-page="currentPage"
                        :per-page="perPage"
                        :filter="filter"
                        :sort-by.sync="sortBy"
                        :sort-desc.sync="sortDesc"
                        @filtered="onFiltered"
                >
                    <template scope="total">

                    </template>
                </b-table>
            </div>

            <b-row>
                <b-col lg="6" >
                    <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" />
                </b-col>
                <b-col lg="6" class="text-right">
                    Showing {{ perPage < totalRows ? perPage : totalRows }} of {{ totalRows }} results
                </b-col>
            </b-row>
        </div>
    </b-card>
</template>

<script>
    import Form from "../classes/Form";
    import FormatsDates from '../mixins/FormatsDates'

    export default {
        mixins: [FormatsDates],

        created() {
            this.loadCaregivers();
            this.fetchData();
        },

        data() {
            return {
                totalRows: 0,
                perPage: 30,
                currentPage: 1,
                sortBy: null,
                sortDesc: false,
                filter: null,
                selectedItem: {},
                fields: [
                    {
                        key: 'firstname',
                        label: 'First Name',
                        sortable: true,
                    },
                    {
                        key: 'lastname',
                        label: 'Last Name',
                        sortable: true,
                    },
                    {
                        key: 'worked',
                        label: 'Worked Hours',
                        sortable: true,
                    },
                    {
                        key: 'scheduled',
                        label: 'Scheduled Hours',
                        sortable: true,
                    },
                    {
                        key: 'total',
                        label: 'Total Expected Hours',
                        sortable: true,
                    },
                ],
                items: [],
                caregivers: [],
                search: {
                    start: '',
                    end: '',
                    caregiver_id: ''
                },
                loading: false,
            }
        },

        methods: {

            async loadCaregivers() {
                const response = await axios.get('/business/caregivers?json=1');
                this.caregivers = response.data;
            },

            fetchData() {
                this.loading = true;
                axios.post('/business/reports/overtime', this.search)
                    .then(response => {
                        this.items = response.data.results.map(function(item) {
                            item['_rowVariant'] = (item.total >= 36) ? (item.total > 40 ? 'danger' : 'warning') : '';
                            return item;
                        });
                        this.totalRows = this.items.length;
                        this.search.start = moment(response.data.date_range[0]).format('L');
                        this.search.end = moment(response.data.date_range[1]).format('L');
                        this.loading = false;
                    }).catch(error => {
                        console.error(error.response);
                        this.loading = false;
                    });
            },

            reset() {
                this.search = {
                    start: '',
                    end: '',
                    caregiver_id: ''
                };
                this.fetchData();
            },

            onFiltered(filteredItems) {
                // Trigger pagination to update the number of buttons/pages due to filtering
                this.totalRows = filteredItems.length;
                this.currentPage = 1;
            }
        }
    }
</script>
