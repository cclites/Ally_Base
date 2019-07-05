<template>
    <b-container fluid>
        <b-row>
            <b-col>

                <date-picker v-model="search.start"
                             placeholder="Start Date"
                             weekStart="1"
                             class="mb-2 mr-2"
                >
                </date-picker>
            </b-col>
            <b-col>
                <date-picker v-model="search.end"
                             placeholder="End Date"
                             class="mb-2 mr-2"
                ></date-picker>
            </b-col>
            <b-col>
                <b-form-group>
                    <select v-model="search.caregiver_id" class="mb-2 mr-2 form-control">
                        <option value="">All Caregivers</option>
                        <option v-for="caregiver in caregivers" :value="caregiver.id" :key="caregiver.id">
                            {{ caregiver.nameLastFirst }}
                        </option>
                    </select>
                </b-form-group>
            </b-col>
            <b-col>
                <select v-model="search.status" class="mb-2 mr-2 form-control">
                    <option value="">All Active/Inactive</option>
                    <option value="active">Active Caregivers</option>
                    <option value="inactive">Inactive Caregivers</option>
                </select>
            </b-col>
            <b-col>
                <business-location-form-group
                        v-model="search.business_id"
                        :allow-all="true"
                        class="mb-2 mr-2"
                        :label="null"
                />
            </b-col>
            <b-col>
                <b-button-group size="sm">
                    <b-btn variant="info" @click="fetchData()" :disabled="loading">Search</b-btn>
                    <b-btn @click="reset()">Reset</b-btn>
                </b-button-group>
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

    </b-container>

</template>

<script>
    import Form from "../classes/Form";
    import FormatsDates from '../mixins/FormatsDates';
    import BusinessLocationSelect from '../components/business/BusinessLocationSelect';
    import BusinessLocationFormGroup from '../components/business/BusinessLocationFormGroup';

    export default {
        mixins: [FormatsDates],
        components: { BusinessLocationFormGroup, BusinessLocationSelect },

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
                        key: 'scheduled',
                        label: 'Scheduled Hours',
                        sortable: true,
                    },
                    {
                        key: 'worked',
                        label: 'Worked Hours',
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
                    start: moment().subtract(7, 'days').format('MM/DD/YYYY'),
                    end: moment().format('MM/DD/YYYY'),
                    caregiver_id: '',
                    business_id: '',
                    status: '',
                    json: 1,
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

                        this.items = response.data.map(function(item) {
                            item['_rowVariant'] = (item.total >= 36) ? (item.total > 40 ? 'danger' : 'warning') : '';
                            return item;
                        });
                        this.totalRows = this.items.length;
                        this.loading = false;
                    }).catch(error => {
                        console.error(error);
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
