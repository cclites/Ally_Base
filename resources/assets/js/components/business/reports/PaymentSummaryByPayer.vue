<template>
    <div>
        <b-row>
            <b-col lg="12">
                <b-card
                        header="Filters"
                        header-text-variant="white"
                        header-bg-variant="info"
                >
                    <b-row>
                        <business-location-form-group
                                v-model="form.business_id"
                                label="Office Location"
                                class="col-md-3"
                                :allow-all="false"
                        />
                        <b-form-group label="Start Date" class="mb-2 mr-2">
                            <date-picker v-model="form.start_date" name="start_date"></date-picker>
                        </b-form-group>
                        <b-form-group label="End Date" class="mb-2 mr-2">
                            <date-picker v-model="form.end_date" name="end_date"></date-picker>
                        </b-form-group>
                        <b-form-group label="Payers" class="col-md-2" :payers="payers">
                            <b-form-select v-model="form.payer_id" label="Payers" class="col-md-2" :payers="payers">
                                <option :value="null" selected>All</option>
                                <option v-for="item in payers" :key="item.id" :value="item.id">{{ item.name }}
                                </option>
                            </b-form-select>
                        </b-form-group>
                        <b-form-group label="Client Type" class="mb-2 mr-2">
                            <b-form-select v-model="form.client_type" name="client_type" :disabled="state === 'loading'">
                                <option value="">All</option>
                                <option v-for="item in clientTypes" :key="item.id" :value="item.id">
                                    {{ item.name }}
                                </option>
                            </b-form-select>
                        </b-form-group>

                        <b-form-group label="Clients" class="mb-2 mr-2">
                            <b-select v-model="form.client" class="mb-2 mr-2">
                                <option value="">All Clients</option>
                                <option v-for="client in clients" :key="client.id" :value="client.id">{{ client.nameLastFirst }}</option>
                            </b-select>
                        </b-form-group>

                        <b-form-group label="Payers" class="mb-2 mr-2">
                            <b-form-select v-model="form.payer" class="mb-2 mr-2" name="payer">
                                <option value="">All Payers</option>
                                <option :value="PRIVATE_PAY_ID">PRIVATE PAY</option>
                                <option :value="OFFLINE_PAY_ID">OFFLINE</option>
                                <option v-for="p in payers" :key="p.id" :value="p.id">{{ p.name }}</option>
                            </b-form-select>
                        </b-form-group>
                    </b-row>

                </b-card>
            </b-col>
        </b-row>
    </div>
</template>

<script>

    import BusinessLocationSelect from "../../business/BusinessLocationSelect";
    import BusinessLocationFormGroup from "../../business/BusinessLocationFormGroup";
    import FormatsNumbers from "../../../mixins/FormatsNumbers";
    import FormatsDates from "../../../mixins/FormatsDates";

    export default {
        name: "PaymentSummaryByPayer",
        components: {BusinessLocationFormGroup, BusinessLocationSelect},
        mixins: [FormatsDates, FormatsNumbers],
        data() {
            return {
                form: new Form({
                    business: '',
                    start: moment().startOf('isoweek').subtract(7, 'days').format('MM/DD/YYYY'),
                    end: moment().startOf('isoweek').subtract(1, 'days').format('MM/DD/YYYY'),
                    client_type: '',
                    client: '',
                    payer: '',
                    json: 1
                }),
                busy: false,
                totalRows: 0,
                perPage: 50,
                currentPage: 1,
                sortBy: 'client_name',
                sortDesc: false,
                fields: [
                    {key: 'client_name', label: 'Client', sortable: true,},
                    {key: 'date', label: 'Date', sortable: true,},
                    {key: 'client_type', label: 'Client Type', sortable: true,},
                    {key: 'payer', label: 'Payer', sortable: true,},
                    {key: 'amount', label: 'Amount', sortable: true,},
                ],
                items: [],
                item: '',
                hasRun: false,
                totals: []
            }
        },
        methods: {
            fetch() {
                this.busy = true;
                this.form.get('/business/reports/payment-summary-by-payer')
                    .then( ({ data }) => {

                        this.items = data.data;
                        this.totalRows = this.items.length;
                    })
                    .catch(e => {})
                    .finally(() => {
                        this.busy = false;
                        this.hasRun = true;
                    })
            },

            print(){
                $(".summary-table").print();
            },

            getClients(){},

            getPayers(){},
        },

        mounted() {
            this.getClients();
            this.getPayers();
        },
    }
</script>

<style scoped>

</style>