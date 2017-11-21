<template>
    <b-card>
        <b-row>
            <b-col xlg="2" lg="3" md="5">
                <b-form-group label="Client" label-for="client_id">
                    <b-form-select
                        id="client_id"
                        name="client_id"
                        v-model="form.client_id"
                        >
                        <option v-for="client in clientsFilter" :value="client.id">{{ client.name }}</option>
                    </b-form-select>
                    <input-help :form="form" field="client_id" text="Select a client to generate caregiver distances from."></input-help>
                </b-form-group>
            </b-col>
            <b-col xlg="2" lg="3" md="4">
                <b-form-group label="Distance" label-for="distance">
                    <b-row>
                        <b-col lg="6">
                            <b-form-select
                                    id="distance"
                                    name="distance"
                                    :options="[2.5,5,10,15,20,25,30,50,100]"
                                    v-model="form.distance"
                            >
                            </b-form-select>
                        </b-col>
                        <b-col lg="6">
                            <b-form-select
                                    id="units"
                                    name="units"
                                    :options="['mi', 'km']"
                                    v-model="form.units"
                            >
                            </b-form-select>
                        </b-col>
                    </b-row>
                    <input-help :form="form" field="distance" text="Select the search radius"></input-help>
                </b-form-group>
            </b-col>
            <b-col xlg="1" lg="2" md="3">
                <b-form-group label="Generate">
                    <b-button id="generateReport" variant="info" @click="generateReport()"><i class="fa fa-refresh fa-spin" v-if="loading"></i> Generate Report</b-button>
                </b-form-group>
            </b-col>
        </b-row>

        <div class="table-responsive">
            <b-table bordered striped hover show-empty
                     :items="items"
                     :fields="fields"
                     :current-page="currentPage"
                     :per-page="perPage"
                     :filter="filter"
                     :sort-by.sync="sortBy"
                     :sort-desc.sync="sortDesc"
                     @filtered="onFiltered"
            >
                <template slot="address1" scope="row">
                    {{ row.item.address.address1 }}
                </template>
                <template slot="zip" scope="row">
                    {{ row.item.address.zip }}
                </template>
                <template slot="actions" scope="row">
                    <b-button :href="'/business/caregivers/' + row.item.id" size="sm">View Caregiver</b-button>
                    <b-button :href="'/business/clients/' + form.client_id" size="sm">View Client</b-button>
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
    </b-card>
</template>

<script>
    import Form from "../classes/Form";

    export default {
        props: {
            'clients': {
                default() {
                    return [];
                }
            },
        },

        data() {
            return {
                totalRows: 0,
                perPage: 30,
                currentPage: 1,
                sortBy: null,
                sortDesc: false,
                filter: null,
                loading: false,
                fields: [
                    {
                        key: 'name',
                        label: 'Caregiver',
                        sortable: true,
                    },
                    {
                        key: 'address1',
                        label: 'Street Address',
                        sortable: true,
                    },
                    {
                        key: 'zip',
                        label: 'Zip',
                        sortable: true,
                    },
                    {
                        key: 'distance',
                        label: 'Distance',
                        sortable: true,
                    },
                    'actions'
                ],
                items: [],
                form: new Form({
                    client_id: null,
                    distance: 10,
                    units: 'mi',
                }),
            }
        },

        mounted() {
            this.totalRows = this.items.length;
        },

        computed: {
            clientsFilter() {
                return _.sortBy(this.clients, ['name']);
            }
        },

        methods: {
            generateReport() {
                let component = this;
                component.loading = true;
                component.form.post('/business/caregivers/distances')
                    .then(function(response) {
                       component.items = response.data.data;
                       component.loading = false;
                    })
                    .catch(function(err) {
                        component.items = [];
                        component.loading = false;
                    })
            },
            onFiltered(filteredItems) {
                // Trigger pagination to update the number of buttons/pages due to filtering
                this.totalRows = filteredItems.length;
                this.currentPage = 1;
            }
        },

        watch: {
            'form.client_id': function() {
                this.items = [];
            }
        }
    }
</script>
