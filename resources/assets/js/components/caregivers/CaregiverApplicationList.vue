<template>
    <b-card>
        <b-row class="mb-3">
            <b-col>
                Application URL: <a :href="'/'+business.id+'/caregiver-application/create'">{{ applicationUrl }}</a>
            </b-col>
        </b-row>
        <b-row class="mb-3">
            <b-col lg="2">
                <b-form-input
                        type="text"
                        id="from-date"
                        class="datepicker"
                        v-model="searchForm.from_date"
                        placeholder="From"
                        @change="filter"
                >
                </b-form-input>
            </b-col>

            <b-col lg="2">
                <b-form-input
                        type="text"
                        id="to-date"
                        class="datepicker"
                        v-model="searchForm.to_date"
                        placeholder="To"
                >
                </b-form-input>
            </b-col>

            <b-col lg="2" class="text-right">
                <b-form-select v-model="searchForm.position" class="mb-3">
                    <template slot="first">
                        <!-- this slot appears above the options from 'options' prop -->
                        <option :value="null">-- Position --</option>
                    </template>
                    <option :value="position.id" v-for="position in positions" :key="position.id">{{ position.name }}</option>
                </b-form-select>
            </b-col>

            <b-col lg="2" class="text-right">
                <b-form-select v-model="searchForm.status" class="mb-3">
                    <template slot="first">
                        <!-- this slot appears above the options from 'options' prop -->
                        <option :value="null">-- Status --</option>
                    </template>
                    <option :value="status.id" v-for="status in statuses" :key="status.id">{{ status.name }}</option>
                </b-form-select>
            </b-col>

            <b-col lg="2">
                <b-button @click="filter" variant="success">
                    Filter
                </b-button>
            </b-col>
        </b-row>

        <div class="table-responsive">
            <b-table bordered striped hover show-empty
                     :items="items"
                     :fields="fields"
                     :current-page="currentPage"
                     :per-page="perPage"
                     :sort-by.sync="sortBy"
                     @filtered="onFiltered"
            >
                <template slot="created_at" scope="data">
                    {{ dateFormat(data.item.created_at) }}
                </template>
                <template slot="name" scope="data">
                    {{ data.item.first_name }} {{ data.item.last_name }}
                </template>
                <template slot="city" scope="data">
                    {{ data.item.city }} {{ data.item.zip }}
                </template>
                <template slot="position" scope="data">
                    <span v-if="data.item.position">{{ data.item.position.name }}</span>
                </template>
                <template slot="status" scope="data">
                    <span v-if="data.item.status">{{ data.item.status.name }}</span>
                </template>
                <template slot="action" scope="data">
                    <a :href="'/business/caregivers/applications/' + data.item.id" class="btn btn-secondary"><i class="fa fa-edit"></i></a>
                </template>
            </b-table>
        </div>

        <b-row>
            <b-col lg="6">
                <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage"/>
            </b-col>
            <b-col lg="6" class="text-right">
                Showing {{ perPage < totalRows ? perPage : totalRows }} of {{ totalRows }} results
            </b-col>
        </b-row>
    </b-card>
</template>

<script>

    import moment from 'moment';

    export default {
        props: {
            'business': Object,
            'applications': Array,
            'positions': Array,
            'statuses': Array
        },

        data() {
            return {
                items: this.applications,
                searchForm: {
                    position: null,
                    status: null
                },
                totalRows: 0,
                perPage: 15,
                currentPage: 1,
                sortBy: null,
                selectedItem: {},
                fields: [
                    {
                        key: 'created_at',
                        label: 'Application Date',
                        sortable: true,
                    },
                    {
                        key: 'name',
                        label: 'Applicant',
                        sortable: true,
                    },
                    {
                        key: 'city',
                        label: 'City & Zip',
                        sortable: true,
                    },
                    {
                        key: 'position',
                        label: 'Position applying for',
                        sortable: true
                    },
                    {
                        key: 'status',
                        label: 'Status',
                        soratable: true
                    },
                    'action'
                ]
            }
        },

        mounted() {
            this.totalRows = this.items.length;

            let fromDate = jQuery('#from-date');
            let toDate = jQuery('#to-date');
            let component = this;
            fromDate.datepicker({
                forceParse: false,
                autoclose: true,
                todayHighlight: true
            }).on("changeDate", function () {
                component.searchForm.from_date = fromDate.val();
            });
            toDate.datepicker({
                forceParse: false,
                autoclose: true,
                todayHighlight: true
            }).on("changeDate", function () {
                component.searchForm.to_date = toDate.val();
            });

        },

        computed: {
            applicationUrl() {
                return window.location.hostname+'/'+this.business.id+'/caregiver-application/create';
            }
        },

        methods: {
            onFiltered(filteredItems) {
                // Trigger pagination to update the number of buttons/pages due to filtering
                this.totalRows = filteredItems.length;
                this.currentPage = 1;
            },
            dateFormat(date) {
                return moment(date).format('L');
            },
            filter() {
                axios.post('/business/caregivers/applications/search', this.searchForm)
                    .then(response => {
                        this.items = response.data;
                    }).catch(error => {
                    console.error(error.response);
                });
            }
        }
    }
</script>
