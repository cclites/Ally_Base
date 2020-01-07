<template>
    <b-card>
        <b-row class="mb-2">
            <b-col lg="8">
                <b-form inline>
                    <b-form-select
                            v-model="businessId"
                    >
                        <option value="">--Filter by Provider--</option>
                        <option v-for="business in businesses" :value="business.id" :key="business.id">{{ business.name }}</option>
                    </b-form-select>
                    <b-form-select id="caregiverId"
                                   v-model="caregiverId"
                    >
                        <option value="">--Filter by Caregiver--</option>
                        <option v-for="caregiver in caregivers" :value="caregiver.id" :key="caregiver.id">{{ caregiver.nameLastFirst }} ({{ caregiver.id }})</option>
                    </b-form-select>
                    <b-form-select id="clientId"
                                   v-model="clientId"
                    >
                        <option value="">--Filter by Client--</option>
                        <option v-for="client in clients" :value="client.id" :key="client.id">{{ client.nameLastFirst }} ({{ client.id }})</option>
                    </b-form-select>
                    <b-btn @click="loadData()" variant="info">Generate</b-btn>
                </b-form>
            </b-col>
            <b-col lg="4" class="text-right">
                <b-form-input v-model="filter" placeholder="Type to Search" />
            </b-col>
        </b-row>

        <loading-card v-show="loading"></loading-card>
        <div class="table-responsive" v-show="!loading">
            <b-table bordered striped hover show-empty
                     :items="items"
                     :fields="fields"
                     :filter="filter"
                     :sort-by.sync="sortBy"
                     :sort-desc.sync="sortDesc"
            >
                <template slot="actions" scope="row">
                </template>
            </b-table>
        </div>
    </b-card>
</template>

<script>
    import FormatsNumbers from "../../mixins/FormatsNumbers";
    import FormatsDates from "../../mixins/FormatsDates";

    export default {
        mixins: [FormatsNumbers, FormatsDates],

        props: {},

        data() {
            return {
                sortBy: null,
                sortDesc: false,
                filter: null,
                items: [],
                fields: [
                    {
                        key: 'id',
                        sortable: true,
                    },
                    {
                        key: 'client_name',
                        sortable: true,
                    },
                    {
                        key: 'caregiver_name',
                        sortable: true,
                    },
                    {
                        key: 'business_name',
                        label: 'Registry',
                        sortable: true,
                    },
                    {
                        key: 'checked_in_time',
                        label: 'Clock In',
                        sortable: true,
                        formatter: (value) => { return this.formatDateTimeFromUTC(value); },
                    },
                    {
                        key: 'caregiver_total',
                        sortable: true,
                        formatter: this.numberFormat
                    },
                    {
                        key: 'provider_total',
                        sortable: true,
                        formatter: this.numberFormat
                    },
                    {
                        key: 'total_cost',
                        sortable: true,
                        formatter: this.numberFormat
                    },
                    {
                        key: 'status',
                        sortable: true,
                    },
                    'actions'
                ],
                businesses: [],
                businessId: "",
                clients: [],
                clientId: "",
                caregivers: [],
                caregiverId: "",
                loading: false,
            }
        },

        mounted() {
            this.loadBusinesses();
            this.loadClients();
            this.loadCaregivers();
        },

        methods: {

            loadData() {
                this.loading = true;
                axios.get('/admin/reports/unpaid_shifts?json=1&business_id=' + this.businessId + '&client_id=' + this.clientId + '&caregiver_id=' + this.caregiverId)
                    .then(response => {
                        this.items = response.data.map(item => {
                            item.caregiver_name = item.caregiver.nameLastFirst;
                            item.client_name = item.client.nameLastFirst;
                            item.business_name = item.business.name;
                            return item;
                        });
                        this.loading = false;
                    });
            },

            loadBusinesses() {
                axios.get('/admin/businesses').then(response => this.businesses = response.data);
            },

            loadClients() {
                axios.get('/admin/clients?json=1').then(response => this.clients = response.data);
            },

            loadCaregivers() {
                axios.get('/admin/caregivers?json=1').then(response => this.caregivers = response.data);
            },

            removeHold(item) {
                let form = new Form();
                let url = '/admin/users/' + item.id + '/hold';
                if (item.type === 'business') {
                    url = '/admin/businesses/' + item.id + '/hold'
                }
                form.submit('delete', url)
                    .then(response => {
                        this.items = this.items.filter(current => current.id !== item.id);
                    });
            },
        },

        watch: {
        }
    }
</script>
