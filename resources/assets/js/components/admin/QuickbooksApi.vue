<template>
    <b-card header="QuickBooks" header-text-variant="white" header-bg-variant="info">
        <b-row>
            <b-col md="6">
                <b-form-group v-if="authorization.auth">
                    <b-btn v-b-modal.invoice_modal variant="info">New Invoice</b-btn>
                </b-form-group>
            </b-col>
            <b-col md="6" class="text-right">
                <b-form-group>
                    <b-btn @click="connection()" variant="success">Connect</b-btn>
                </b-form-group>
            </b-col>
        </b-row>

        <div class="table-responsive">
            <b-table bordered striped hover show-empty
                 :items="items"
                 :fields="fields"
                 :current-page="currentPage"
                 :per-page="perPage"
                 :sort-by.sync="sortBy"
                 :sort-desc.sync="sortDesc">
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

        <b-modal id="invoice_modal" ref="invoiceModal" title="New Invoice" ok-title="Save" @ok="validateBeforeSubmit" @cancel="clearInvoiceData" :ok-disabled="loading">
            <b-form-group label="Customers *" label-size="md">
                <b-form-select v-model="selectedCustomer" v-validate="'required'" name="customers">
                    <option value="">Please Select</option>
                    <option v-for="customer of customers" :value="customer['Id']">
                        {{ customer['GivenName'] }} {{ customer['MiddleName'] }} {{ customer['CompanyName'] ? '(' + customer['CompanyName'] + ')' : '' }}
                    </option>
                </b-form-select>
                <p class="text-danger" v-if="errors.has('customers')">{{ errors.first('customers') }}</p>
            </b-form-group>

            <b-form-group label="Amount *" label-size="md">
                <b-form-input v-model="amount" v-validate="'required'" name="amount"></b-form-input>
                <p class="text-danger" v-if="errors.has('amount')">{{ errors.first('amount') }}</p>
            </b-form-group>

            <b-form-group label="Description" label-size="md">
                <b-form-textarea v-model="description" name="description" :rows="3" :max-rows="6"></b-form-textarea>
                <p class="text-danger" v-if="errors.has('description')">{{ errors.first('description') }}</p>
            </b-form-group>

            <div class="loader" v-if="loading"></div>
        </b-modal>
    </b-card>
</template>

<script>

    export default {

        props: {
            connect: Object,
            authorization: Object,
            invoices: Array,
            customers: Array
        },

        data() {
            return {
                selectedCustomer: '',
                amount: '',
                description: '',
                loading: false,

                /*table params*/
                totalRows: 0,
                perPage: 15,
                currentPage: 1,
                sortBy: 'date',
                sortDesc: true,
                fields: [
                    {
                        key: 'date',
                        label: 'Date',
                        sortable: true,
                    },
                    {
                        key: 'customer',
                        label: 'Customer',
                        sortable: true,
                    },
                    {
                        key: 'amount',
                        label: 'Amount',
                        sortable: true,
                    },
                    {
                        key: 'description',
                        label: 'Description',
                        sortable: true,
                    },
                ],
                items: [],
                invoicesArray: []
            }
        },

        mounted() {
            this.checkStatus();
            this.totalRows = this.items.length;
            this.invoicesArray = this.invoices;
            this.items = this.invoiceData();
        },

        methods: {
            connection() {
                window.location.href = 'quickbooks-api/connection'
            },

            validateBeforeSubmit(evt) {
                evt.preventDefault()
                this.$validator.validateAll().then((result) => {
                    if (result) {
                        this.createInvoice();
                    }
                });
            },

            createInvoice() {
                if (this.selectedCustomer != '' && this.amount != '') {
                    this.loading = true;
                    let data = {};

                    this.customers.forEach(customer => {
                        if(customer['Id'] == this.selectedCustomer) {
                            data.customer = customer['Id'];
                            data.customer_name = customer['GivenName'];
                        }
                    });

                    data.amount = this.amount;
                    data.description = this.description;

                    axios.post('quickbooks-api/create-invoice', data).then(res => {
                        if(res.status == 200) {
                            this.invoicesArray = JSON.parse(res.data);
                            this.items = this.invoiceData();

                            this.$refs.invoiceModal.hide();
                            this.clearInvoiceData();
                        } else {
                            alerts.addMessage('error', res.data);
                        }
                        this.loading = false;

                    }).catch(err => {
                        if(err.response.data && err.response.data.errors) {
                            let errors = err.response.data.errors;
                            for(let index in errors) {
                                alerts.addMessage('error', errors[index][0]);
                            }
                        }

                        this.loading = false;
                    });
                } else {
                    alerts.addMessage('error', 'Please fill required fields');
                }
            },

            invoiceData() {
                return this.invoicesArray.map(invoice => {
                    let customerName = 'N/A';
                    if(invoice.CustomerRef) {
                        this.customers.forEach(customer => {
                            if(invoice.CustomerRef == customer.Id) {
                                let middleName =  customer['MiddleName'] ?  customer['MiddleName'] : '';
                                let companyName = customer['CompanyName'] ? '(' + customer['CompanyName'] + ')' : '';
                                customerName =  customer['GivenName'] + ' ' + middleName + ' ' + companyName;
                            }
                        });
                    }

                    return {
                        date: moment(invoice.MetaData['CreateTime']).format('MM/DD/YYYY HH:mm'),
                        customer: customerName,
                        amount: (invoice.Line[0] ? invoice.Line[0]['Amount'] : 'N/A'),
                        description: (invoice.Line[0] ? invoice.Line[0]['Description'] : 'N/A'),
                    }
                })
            },

            checkStatus() {
                setTimeout(() => {
                    if (this.connect) {
                        if (this.connect['status'] == 'failed') {
                            alerts.addMessage('error', 'Connection failed');
                        } else if (this.connect['status'] == 'connected') {
                            alerts.addMessage('success', 'Successfully connected');
                        }
                    }
                })
            },

            clearInvoiceData() {
                this.selectedCustomer = '';
                this.amount = '';
                this.description = '';

                this.$validator.reset();
            }
        }
    }
</script>

<style lang="scss">
    .loader {
        border: 10px solid #f3f3f3;
        border-radius: 50%;
        border-top: 10px solid #1e88e5;
        width: 80px;
        height: 80px;
        -webkit-animation: spin 2s linear infinite;
        animation: spin 2s linear infinite;
        margin: 0 auto;
    }

    @-webkit-keyframes spin {
        0% {
            -webkit-transform: rotate(0deg);
        }
        100% {
            -webkit-transform: rotate(360deg);
        }
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }
        100% {
            transform: rotate(360deg);
        }
    }
</style>