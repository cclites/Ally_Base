<template>
    <b-card>
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
                        <option v-for="row in clients" :value="row.id" :key="row.id">{{ row.name }}</option>
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
            <div class="table-responsive">
                <b-table bordered striped hover show-empty
                         :items="items"
                         :fields="fields"
                         :current-page="currentPage"
                         :per-page="perPage"
                         :sort-by.sync="sortBy"
                         :sort-desc.sync="sortDesc"
                         class="report-table"
                >
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
    import BusinessLocationSelect from '../../../components/business/BusinessLocationSelect';
    import BusinessLocationFormGroup from '../../../components/business/BusinessLocationFormGroup';

    export default {
        name: "ClientReferralsReport",
        mixins: [FormatsDates],
        components: { BusinessLocationFormGroup, BusinessLocationSelect },

        props: {
            'clients' : null,
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
                        key: 'name',
                        label: 'Client',
                        sortable: true,
                    },
                    {
                        key: 'county',
                        label: 'County',
                        sortable: true,
                    },
                    {
                        key: 'payer',
                        label: 'Payer',
                        sortable: true,
                    },
                    {
                        key: 'date',
                        label: 'Date',
                        sortable: true,
                    },
                ],
                'items' : [],
            };
        },

        methods: {

            fetch(){
                this.loading = true;
                this.form.get('/business/reports/client-referrals')
                    .then( ({ data }) => {
                        this.items = data
                        this.totalRows = this.items.length;
                    })
                    .catch(e => {})
                    .finally(() => {
                        this.loading = false;
                    })
            },

            print(){
                $(".report-table").print();
            },
        },
    }
</script>

<style scoped>

</style>