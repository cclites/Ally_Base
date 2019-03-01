<template>
    <b-card :title="title">
        <b-row>
            <b-col lg="8">
                <b-form inline>
                    <b-form-select v-model="caregiverId">
                        <option :value="null">All Caregivers</option>
                        <option v-for="caregiver in caregivers" :value="caregiver.id">{{ caregiver.nameLastFirst }}</option>
                    </b-form-select>
                    <b-form-select v-model="clientId">
                        <option :value="null">All Clients</option>
                        <option v-for="client in clients" :value="client.id">{{ client.nameLastFirst }}</option>
                    </b-form-select>
                </b-form>
            </b-col>
            <b-col lg="4" class="text-right">
                <b-btn :href="`/business/statements/deposits/${deposit.id}/xls`" variant="success">Export to Excel</b-btn>
                <b-btn :href="`/business/statements/deposits/${deposit.id}/pdf`"><i class="fa fa-file-pdf-o"></i> PDF Statement</b-btn>
            </b-col>
        </b-row>


        <b-table bordered striped hover show-empty
                 :items="items"
                 :fields="fields"
                 :sort-by.sync="sortBy"
                 :sort-desc.sync="sortDesc"
                 empty-text="No items are available"
        >
        </b-table>

    </b-card>

</template>

<script>
    import FormatsNumbers from "../../../mixins/FormatsNumbers";
    import FormatsDates from "../../../mixins/FormatsDates";

    export default {
        name: "ItemizedPayment",

        mixins: [FormatsNumbers, FormatsDates],

        props: {
            deposit: {
                type: Object,
                required: true,
            },
            invoices: {
                type: Array,
                required: true,
            }
        },

        computed: {
            items() {
                let filterFn = (item) => {
                    if (!this.caregiverId && !this.clientId) {
                        return true;
                    }
                    if (!item.invoiceable) {
                        return false;
                    }
                    if (this.caregiverId && parseInt(item.invoiceable.caregiver_id) !== parseInt(this.caregiverId)) {
                        return false;
                    }
                    if (this.clientId && parseInt(item.invoiceable.client_id) !== parseInt(this.clientId)) {
                        return false;
                    }
                    return true;
                };

                return this.invoices.reduce((items, invoice) => {
                    return [...items, ...invoice.items.filter(filterFn)];
                }, [])
            },

            title() {
                let title = `Itemized View of Deposit #${this.deposit.id}. Deposit Amount: $${this.numberFormat(this.deposit.amount)}`;
                if (this.deposit.amount < 0) {
                    title += " (Withdrawal)";
                }
                return title;
            },
        },

        data() {
            return {
                caregivers: [],
                clients: [],
                clientId: null,
                caregiverId: null,
                fields: [
                    {
                        key: "date",
                        formatter: val => this.formatDateFromUTC(val),
                        sortable: true,
                    },
                    {
                        key: "group",
                        sortable: true,
                    },
                    {
                        key: "name",
                        label: "Service Name",
                        sortable: true,
                    },
                    {
                        key: "units",
                        formatter: val => this.numberFormat(val),
                        sortable: true,
                    },
                    {
                        key: "client_rate",
                        formatter: val => this.numberFormat(val),
                        sortable: true,
                    },
                    {
                        key: "caregiver_rate",
                        formatter: val => this.numberFormat(val),
                        sortable: true,
                    },
                    {
                        key: "ally_rate",
                        formatter: val => this.numberFormat(val),
                        sortable: true,
                    },
                    {
                        key: "rate",
                        formatter: val => this.numberFormat(val),
                        sortable: true,
                    },
                    {
                        key: "total",
                        formatter: val => this.numberFormat(val),
                        sortable: true,
                    },
                ],
                sortBy: 'date',
                sortDesc: false,
            }
        },

        methods: {
            async loadClients() {
                const response = await axios.get("/business/clients?json=1");
                this.clients = response.data;
            },
            async loadCaregivers() {
                const response = await axios.get("/business/caregivers?json=1");
                this.caregivers = response.data;
            },
        },

        mounted() {
            this.loadClients();
            this.loadCaregivers();
        }
    }
</script>

<style scoped>

</style>