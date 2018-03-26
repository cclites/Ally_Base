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
                                {{ caregiver.name }}
                            </option>
                        </b-form-select>
                    </b-input-group>

                    <b-button-group size="sm">
                        <b-btn variant="info" @click="fetchData()">Search</b-btn>
                        <b-btn @click="reset()">Reset</b-btn>
                    </b-button-group>
                </b-button-toolbar>
            </b-col>
        </b-row>

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
    </b-card>
</template>

<script>
    import Form from "../classes/Form";
    import FormatsDates from '../mixins/FormatsDates'

    export default {
        mixins: [FormatsDates],

        created() {
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
                ],
                items: [],
                search: {
                    start: '',
                    end: '',
                    caregiver_id: ''
                }
            }
        },

        methods: {

            fetchData() {
                axios.post('/business/reports/overtime', this.search)
                    .then(response => {
                        this.items = response.data.results.map(function(caregiver) {
                            return {
                                _rowVariant: (caregiver.total >= 36) ? (caregiver.total > 40 ? 'danger' : 'warning') : '',
                                id: caregiver.user.id,
                                firstname: caregiver.user.firstname,
                                lastname: caregiver.user.lastname,
                                worked: caregiver.worked,
                                scheduled: caregiver.scheduled,
                                total: caregiver.total,
                            }
                        });
                        this.totalRows = this.items.length;
                        this.search.start = moment(response.data.date_range[0]).format('L');
                        this.search.end = moment(response.data.date_range[1]).format('L');
                    }).catch(error => {
                        console.error(error.response);
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
        },

        computed: {
            caregivers() {
                return _.map(this.items, function (item) {
                    return {
                        name: item.firstname + ' ' + item.lastname,
                        id: item.id
                    }
                });
            }
        }
    }
</script>
