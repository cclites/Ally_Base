<template>
    <b-card
            header="Client Caregiver Assignments"
            header-text-variant="white"
            header-bg-variant="info"
    >
        <b-row class="mb-2">
            <b-col sm="6">
                <b-row>
                    <b-col cols="6">
                        <b-form-select v-model="filterCaregiverId">
                            <option value="">All Caregivers</option>
                            <option v-for="item in caregivers" :value="item.id" :key="item.id">{{ item.nameLastFirst }}</option>
                        </b-form-select>
                    </b-col>
                    <b-col cols="6">
                        <b-form-select v-model="filterClientId">
                            <option value="">All Clients</option>
                            <option v-for="item in clients" :value="item.id" :key="item.id">{{ item.nameLastFirst }}</option>
                        </b-form-select>
                    </b-col>
                </b-row>
            </b-col>
            <b-col sm="6" class="d-flex justify-content-end">

                <b-button variant="info" @click=" loadData() ">

                    Generate Report
                </b-button>
            </b-col>
        </b-row>

        <loading-card v-show="loading"></loading-card>

        <div v-show="! loading" class="table-responsive">
            <b-table bordered striped hover show-empty
                     :fields="fields"
                     :items="filteredItems"
                     :sort-by.sync="sortBy"
                     :sort-desc.sync="sortDesc"
                     class="shift-table"
            >
                <template slot="caregiver_rate" scope="data">{{ numberFormat(data.value) }}</template>
                <template slot="provider_fee" scope="data">{{ numberFormat(data.value) }}</template>
                <template slot="ally_fee" scope="data">{{ numberFormat(data.value) }}</template>
                <template slot="total_hourly" scope="data">{{ numberFormat(data.value) }}</template>
                <template slot="payment_fee" scope="data">{{ percentageFormat(data.value) }}</template>
                <template slot="actions" scope="row">
                    <b-btn :href="'/business/clients/' + row.item.client_id + '#caregivers'">View Client Tab</b-btn>
                </template>
            </b-table>
        </div>
    </b-card>
</template>

<script>
    import FormatsNumbers from '../mixins/FormatsNumbers';

    export default {
        mixins: [FormatsNumbers],

        props: {},

        data() {
            return {
                items: [],
                sortBy: null,
                loading: false,
                sortDesc: null,
                clients: [],
                caregivers: [],
                filterCaregiverId: "",
                filterClientId: "",
            }
        },

        mounted() {
            this.loadFiltersData();
        },

        computed: {
            fields() {
                let fields = [];
                let item;
                if (item = this.items[0]) {
                    for (let key of Object.keys(item)) {
                        if (key === 'client_id') continue;
                        if (key === 'caregiver_id') continue;
                        fields.push({
                            'key': key,
                            'sortable': true,
                        });
                    }

                }
                fields.push({
                    key: 'actions',
                    class: 'hidden-print'
                });
                return fields;
            },
            filteredItems() {
                let items = this.items;
                if (this.filterCaregiverId || this.filterClientId) {
                    items = items.filter(item => {
                        if (this.filterCaregiverId && this.filterCaregiverId != item.caregiver_id) return false;
                        if (this.filterClientId && this.filterClientId != item.client_id) return false;
                        return true;
                    });
                }
                return items;
            }
        },

        methods: {
            loadData() {
                this.loading = true;
                let component = this;
                axios.get('/business/reports/client_caregivers?json=1')
                    .then(function(response) {
                        if (Array.isArray(response.data)) {
                            component.items = response.data;
                        }
                        else {
                            component.items = [];
                        }
                        this.loading = false;
                    })
                    .catch(e => {
                        this.loading = false;
                    });
            },

            loadFiltersData() {
                axios.get('/business/clients').then(response => this.clients = response.data);
                axios.get('/business/caregivers').then(response => this.caregivers = response.data);
            },
        },
    }
</script>

<style>
    table {
        font-size: 14px;
    }
</style>
