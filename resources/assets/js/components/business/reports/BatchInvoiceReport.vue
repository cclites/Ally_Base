<template>

    <b-card header="Batch Invoice Report"
            header-text-variant="white"
            header-bg-variant="info"
    >
        <div class="row">
            <div class="col-lg-12">
                <b-form inline>
                    <date-picker
                            v-model="form.start"
                            placeholder="Start Date"
                            name="start"
                            class="mr-2 mb-1"
                    >
                    </date-picker> -
                    <date-picker
                            v-model="form.end"
                            placeholder="End Date"
                            name="end"
                            class="ml-1 mr-2 mb-1"
                    >
                    </date-picker>
                    <business-location-form-group
                            v-model="form.business"
                            :label="null"
                            class="mr-2 mb-1"
                            :allow-all="true"
                    />
                    <b-select v-model="form.client" class="mb-1 mr-2">
                        <option value="">All Clients</option>
                        <option v-for="client in clients" :key="client.id" :value="client.id">{{ client.nameLastFirst }}</option>
                    </b-select>

                    <b-form-select v-model="form.type" class="mb-1 mr-2">
                        <option v-for="item in clientTypes" :key="item.value" :value="item.value">{{ item.text }}</option>
                    </b-form-select>

                    <b-form-select v-model="form.active" class="mr-2 mb-1" name="client_status">
                        <option value="">All Active/Inactive Clients</option>
                        <option :value="1">Active</option>
                        <option :value="0">Inactive</option>
                    </b-form-select>

                    <b-button-group class="mb-1">
                        <b-button @click="generateReport()" variant="info" :disabled="loading"><i class="fa fa-file-pdf-o mr-1"></i>Generate Report</b-button>
                        <b-button @click="printInvoices()"><i class="fa fa-print mr-1"></i>Print</b-button>
                    </b-button-group>
                </b-form>
            </div>
        </div>

        <div class="row">
            <div class="table-responsive">
                <b-table bordered striped hover show-empty
                         :fields="fields"
                         :items="items"
                         class="report-table"
                >
                </b-table>
            </div>
        </div>

    </b-card>




</template>

<script>

    import BusinessLocationFormGroup from '../../business/BusinessLocationFormGroup';
    import Constants from '../../../mixins/Constants';
    import DatePicker from '../../DatePicker';

    export default {
        components: { BusinessLocationFormGroup, DatePicker },
        mixins: [Constants],

        props: {
            clients: {
                type: [Object, Array],
                required: true,
                default: () => { return []; },
            },
        },

        data() {
            return {
                form: new Form({
                    business: '',
                    start: moment().subtract(30, 'days').format('MM/DD/YYYY'),
                    end: moment().format('MM/DD/YYYY'),
                    type: '',
                    client: '',
                    active: '',
                    json: 1,
                }),
                loading: false,
                fields: [
                    {
                        key: 'invoice_id',
                        label: 'Invoice #',
                    },
                    {
                        key: 'created_at',
                        label: 'Created',
                    },
                    {
                        key: 'client',
                        label: 'Client',
                        sortable: true,
                    },
                    {
                        key: 'amount',
                        label: 'Amount',
                        sortable: true,
                    },
                ],
                items: null,
            }
        },

        methods: {

            async generateReport() {

                this.loading = true;
                this.form.get('/business/reports/batch-invoice')
                    .then( ({ data }) => {
                        this.items = data;
                    })
                    .catch(() => {})
                    .finally(() => {
                        this.loading = false;
                    });
            },

            printInvoices(){
                let invoiceIds = [];

                for (const [key, value] of Object.entries(this.items)) {
                    invoiceIds.push(value.invoice_id);
                }

                let url = '/business/reports/batch-invoice/print/?ids=' + invoiceIds;

                var link=document.createElement('a');
                document.body.appendChild(link);
                link.href=url ;
                link.click();

                /*
                axios.get('/business/reports/batch-invoice/print/?ids=' + invoiceIds)
                    .then( ({ data }) => {

                    })
                    .catch(() => {})
                    .finally(() => {
                        this.loading = false;
                    });
                 */
            }
        },
    }
</script>

<style scoped>

</style>