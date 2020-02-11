<template>
    <b-card>
        <b-row class="mb-3">
            <b-col>
                Application URL: <a :href="applicationUrl">{{ applicationUrl }}</a>
            </b-col>
        </b-row>
        <b-row class="mb-3">
            <b-col lg="2">
                From: <date-picker v-model="start_date" placeholder="From" />
            </b-col>

            <b-col lg="2">
                To: <date-picker v-model="end_date" placeholder="To" />
            </b-col>

            <b-col lg="2">
                Status: <b-form-select v-model="status" class="mb-3">
                    <template slot="first">
                        <!-- this slot appears above the options from 'options' prop -->
                        <option value="">-- All Statuses --</option>
                    </template>
                    <option v-for="status in statuses" :key="status">{{ status }}</option>
                </b-form-select>
            </b-col>

            <b-col lg="1">
                <b-form-group>
                    <b-form-checkbox id="archived"
                                     v-model="archived"
                                     :value="1"
                                     :unchecked-value="0"
                                     class="mt-4"
                    >
                        Archived
                    </b-form-checkbox>
                </b-form-group>
            </b-col>

            <b-col lg="2">
                Filter:<br />
                <b-button @click="reloadData" variant="info">
                    Generate Report
                </b-button>
            </b-col>
            <b-col lg="2">
                Applicant Name:<br />
                <b-form-input v-model="search" placeholder="Type to Search" class="f-1" />
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
                        :sort-by.sync="sortBy"
                        @filtered="onFiltered"
                         ref="table"
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
                    <template slot="action" scope="data">
                        <a :href="'/business/caregivers/applications/' + data.item.id" class="btn btn-secondary"><i class="fa fa-eye"></i></a>
                        <a :href="'/business/caregivers/applications/' + data.item.id + '/edit'" class="btn btn-secondary"><i class="fa fa-edit"></i></a>
                        <button variant="danger" @click="deleteApplication(data.item.id)" class="btn btn-danger"><i class="fa fa-times mr-1 pl-1"></i></button>
                        <button @click="convertApplication(data.item.id)" class="btn btn-info"><i class="fa fa-plus mr-1"></i>Convert</button>
                        <button @click="archiveApplication(data.item.id)" class="btn btn-info" v-if="!data.item.archived"><i class="fa fa-archive mr-1"></i>Archive</button>
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
        </div>
    </b-card>
</template>

<script>

    import moment from 'moment';

    export default {
        props: {
            'applicationUrl': String,
        },

        data() {
            return {
                items: [],
                statuses: ['New', 'Open', 'Converted'],
                start_date: "",
                end_date: "",
                status: '',
                archived: 0,
                totalRows: 0,
                perPage: 15,
                currentPage: 1,
                search: '',
                sortBy: null,
                selectedItem: {},
                loading: false,
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
                        sortable: true
                    },
                    'action'
                ]
            }
        },
        watch: {
            search: _.debounce(function(){
                this.reloadData();
            }, 300)
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
            reloadData() {
                this.loading = true;
                let url = `/business/caregivers/applications?json=1&start_date=${this.start_date}&end_date=${this.end_date}&status=${this.status}&archived=${this.archived}&search=${this.search}`;
                axios.get(url)
                    .then(response => {
                        this.items = response.data;
                        this.loading = false;
                    })
                    .catch(error => {
                        this.loading = false;
                        console.error(error.response);
                    });
            },
            convertApplication(id) {
                if (confirm('Are you sure you wish to convert this application into an active caregiver?')) {
                    let url = `/business/caregivers/applications/${id}/convert`;
                    let form = new Form({});
                    form.post(url);
                }
            },
            deleteApplication(id){
                if (confirm('Are you sure you wish to delete this application?')) {
                    let url = `/business/caregivers/applications/${id}/delete`;
                    let form = new Form({});
                    form.post(url)
                        .then(response => {
                            this.items.splice(this.items.id, 1);
                            this.$refs.table.refresh();
                        });
                }
            },
            archiveApplication(id){
                if (confirm('Are you sure you wish to archive this application?')) {
                    let url = `/business/caregivers/applications/${id}/archive`;
                    let form = new Form({});
                    form.post(url)
                        .then(response => {
                            this.items.splice(this.items.id, 1);
                            this.$refs.table.refresh();
                        });
                }
            },

        }
    }
</script>
