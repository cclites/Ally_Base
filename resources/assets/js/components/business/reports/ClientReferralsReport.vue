<template>
    <b-card
      title="This report shows data that has been invoiced & billed. No data for the current service week will be included."
    >
        <b-row>
            <b-form-group label="Location" class="mb-2 mr-2">
                <business-location-form-group
                        v-model="form.business"
                        :allow-all="false"
                        :label="null"
                />
            </b-form-group>
            <b-form-group label="Start Date" class="mb-2 mr-2">
                <date-picker v-model="form.start"
                             weekStart="1"
                             :label="null"
                >
                </date-picker>
            </b-form-group>
            <b-form-group label="End Date" class="mb-2 mr-2">
                <date-picker v-model="form.end"
                             class="mb-2 mr-2"
                ></date-picker>
            </b-form-group>
            <b-form-group label="Clients" class="mb-2 mr-2">
                    <b-form-select
                            name="client_id"
                            v-model="form.client"
                    >
                        <option value="">All Clients</option>
                        <option v-for="row in clients" :value="row.id" :key="row.id" :text="row.name">{{ row.name }}</option>
                    </b-form-select>
            </b-form-group>
            <b-form-group label="Salesperson" class="mb-2 mr-2" v-if="salespersons">
                <b-form-select v-model="form.salesperson" class="mb-2 mr-2" name="salesperson">
                    <option value="">All Salespeople</option>
                    <option v-for="s in salespersons" :key="s.id" :value="s.id">{{ s.name }}</option>
                </b-form-select>
            </b-form-group>
            <b-form-group label="County" class="mb-2 mr-2">
                <b-form-input type="text" v-model="form.county" placeholder="County"/>
            </b-form-group>
            <b-form-group label="&nbsp;" class="mb-2 mr-2">
                <b-button-group>
                    <b-btn variant="info" @click="fetch()" :disabled="loading">Generate Report</b-btn>
                    <b-btn @click="print()">Print</b-btn>
                </b-button-group>
            </b-form-group>
        </b-row>

        <loading-card v-show="loading"></loading-card>

        <div v-show="!loading">
            <div class="table-responsive" >
                <b-table bordered striped hover show-empty
                         :items="items"
                         :fields="fields"
                         :current-page="currentPage"
                         :per-page="perPage"
                         :sort-by.sync="sortBy"
                         :sort-desc.sync="sortDesc"
                         class="report-table"
                         :footClone="footclone"
                         :noFooterSorting="true"
                >
                    <template slot="FOOT_location" scope="item">
                        <strong>For Location: </strong>{{ totals.location }}
                    </template>

                    <template slot="FOOT_county" scope="item">
                        <strong>Start Date: </strong>{{ totals.start }}
                    </template>

                    <template slot="FOOT_client" scope="item">
                        <strong>End Date: </strong>{{ totals.end }}
                    </template>
                    <template slot="FOOT_name" scope="item">
                        <strong>For Client: </strong>{{ totals.client ? totals.client : 'All Clients' }}
                    </template>

                    <template slot="FOOT_date" scope="item">
                        <strong>For County: </strong>{{ totals.county ? totals.county : 'All Counties' }}
                    </template>

                    <template slot="FOOT_payer" scope="item"></template>

                    <template slot="FOOT_revenue" scope="item">
                        <strong>Revenue: </strong>{{ totals.revenue }}
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
    import FormatsDates from '../../../mixins/FormatsDates';
    import FormatsNumbers from '../../../mixins/FormatsNumbers';
    import BusinessLocationSelect from '../../../components/business/BusinessLocationSelect';
    import BusinessLocationFormGroup from '../../../components/business/BusinessLocationFormGroup';

    export default {
        name: "ClientReferralsReport",
        mixins: [FormatsDates, FormatsNumbers],
        components: { BusinessLocationFormGroup, BusinessLocationSelect },

        data() {
            return {
                form: new Form(
                    {
                        'json': 1,
                        'start': moment().subtract(6, 'days').format('MM/DD/YYYY'),
                        'end' : moment().format('MM/DD/YYYY'),
                        'business' : '',
                        'client' : '',
                        'county' : '',
                        'salesperson': '',
                    }
                ),
                loading: false,
                totalRows: 0,
                perPage: 30,
                currentPage: 1,
                sortBy: '',
                sortDesc: '',
                fields: [
                    {
                        key: 'location',
                        label: 'Location',
                        sortable: true,
                    },
                    {
                        key: 'county',
                        label: 'County',
                        sortable: true,
                    },
                    {
                        key: 'name',
                        label: 'Client',
                        sortable: true,
                    },
                    {
                        key: 'date',
                        label: 'Date Created',
                        sortable: true,
                    },
                    {
                        key: 'salesperson',
                        label: 'Salesperson',
                        sortable: true,
                    },
                    {
                        key: 'payer',
                        label: 'Payer',
                        sortable: true,
                    },
                    {
                        key: 'revenue',
                        label: 'Revenue',
                        formatter: val => this.moneyFormat(val),
                        sortable: true,
                    },
                ],
                totals: '',
                items : [],
                clients : [],
                clientName: '',
                salespersons: '',
                location: '',
                footclone: false,
                onFirstLoad: true,
            };
        },

        methods: {

            fetch(){
                this.loading = true;

                this.form.get('/business/reports/client-referrals')
                    .then( ({ data }) => {
                        this.items = data.data;
                        this.totals = data.totals;
                        this.totalRows = this.items.length;
                    })
                    .catch(e => {})
                    .finally(() => {
                        this.loading = false;
                        this.footclone = true;
                    })
            },
            print(){
                $(".report-table").print();
            },
            getClients(){
                axios.get('/business/dropdown/clients?businesses=' + this.form.business)
                    .then( ({ data }) => {
                        this.clients = data;
                    })
                    .catch(e => {})
                    .finally(() => {
                    })
            },
            getSalespeople(){
                axios.get('/business/dropdown/sales-people')
                    .then( ({ data }) => {
                        this.salespersons = data;
                    })
                    .catch(e => {})
                    .finally(() => {
                    })
            },
        },
        watch: {
            async 'form.business'(newValue, oldValue) {
                if(this.onFirstLoad){
                    this.onFirstLoad = false;
                }else if(newValue != oldValue){
                    this.getClients();
                    this.getSalespeople();
                }

            },
        },
        mounted() {
            this.$nextTick(function(){
                this.getClients();
                this.getSalespeople();
            })
        }
    }
</script>

<style scoped>

</style>