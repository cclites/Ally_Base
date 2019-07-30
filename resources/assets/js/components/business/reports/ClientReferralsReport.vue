<template>
    <b-card
      title="This report shows data that has been invoiced & billed. No data for the current service week will be included."
    >
        <b-row>
            <b-col>
                <business-location-form-group
                        v-model="form.business"
                        :allow-all="false"
                        class="mb-2 mr-2"
                        :label="null"
                />
            </b-col>
            <b-col>
                <date-picker v-model="form.start"
                             placeholder="Start Date"
                             weekStart="1"
                             class="mb-2 mr-2"
                >
                </date-picker>
            </b-col>
            <b-col>
                <date-picker v-model="form.end"
                             placeholder="End Date"
                             class="mb-2 mr-2"
                ></date-picker>
            </b-col>
            <b-col>
                    <b-form-select
                            name="client_id"
                            v-model="form.client"
                    >
                        <option value="">All Clients</option>
                        <option v-for="row in clients" :value="row.id" :key="row.id" :text="row.name">{{ row.name }}</option>
                    </b-form-select>
            </b-col>
            <b-col>
                <b-form-input type="text" v-model="form.county" placeholder="County"/>
            </b-col>
            <b-col>
                <b-button-group size="sm">
                    <b-btn variant="info" @click="fetch()" :disabled="loading">Generate Report</b-btn>
                    <b-btn @click="print()">Print</b-btn>
                </b-button-group>
            </b-col>
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

        props: {

        },

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
                location: '',
                footclone: false,
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

            loadClients(){
                this.loading = true;
                axios.get('/business/reports/client-referrals/' + this.form.business)
                    .then( ({ data }) => {
                        this.clients = data;
                    })
                    .catch(e => {})
                    .finally(() => {
                        this.loading = false;
                    })
                this.loading = false;
            },

            computeCurrentServiceWeekStart(){
                //set the max for the end date, and then set the start date based on that.
            }
        },

        watch: {
            async 'form.business'(newValue, oldValue) {
                if (newValue != oldValue) {
                    await this.loadClients();
                }
            },
        },

        mounted() {
            this.$nextTick(function(){
                this.loadClients();
            })
        }
    }
</script>

<style scoped>

</style>