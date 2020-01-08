<template>
    <div>
        <b-row>
            <b-col lg="12">
                <b-card
                    header="Client Service Coordinators Report"
                    header-text-variant="white"
                    header-bg-variant="info"
                >
                    <b-row>
                        <b-col lg="2">
                            <business-location-form-group label="Office Location" v-model="filters.businesses" :allow-all="true" class="f-1" />
                        </b-col>
                        <b-col lg="2">
                            <b-form-group label="Service Coordinator">
                                <b-form-select v-model="filters.services_coordinator_id" name="services_coordinator_id">
                                    <option value="">All Service Coordinators</option>
                                    <option v-for="item in servicesCoordinators" :key="item.id" :value="item.id">{{ item.nameLastFirst }}</option>
                                </b-form-select>
                            </b-form-group>
                        </b-col>
                        <b-col lg="2">
                            <b-form-group label="Client">
                                <label v-if="!clientsLoaded">Loading...</label>
                                <b-form-select v-else v-model="filters.client_id" name="client_id">
                                    <option value="">All Clients</option>
                                    <option v-for="item in clients" :key="item.id" :value="item.id">{{ item.name }}</option>
                                </b-form-select>
                            </b-form-group>
                        </b-col>
                        <b-col lg="2">
                            <b-form-group label="Client Status">
                                <b-form-select v-model="filters.client_status" name="client_status">
                                    <option value="">All Clients</option>
                                    <option :value="1">Active</option>
                                    <option :value="0">Inactive</option>
                                </b-form-select>
                            </b-form-group>
                        </b-col>
                        <b-col lg="2">
                            <b-form-group label="Days since (max)">
                                <b-form-input v-model="filters.days_since_contact" type="number" placeholder="Last contact" />
                            </b-form-group>
                        </b-col>
                        <b-col lg="2">
                            <b-form-group label="&nbsp;">
                                <b-button variant="info" @click="fetch()" :disabled="filters.busy">Generate Report</b-button>
                            </b-form-group>
                        </b-col>
                    </b-row>
                    <hr/>

                    <loading-card v-if="filters.busy"></loading-card>
                    <div v-else>
                        <div class="table-responsive">
                            <b-table 
                                bordered striped hover show-empty
                                :items="items"
                                :fields="fields"
                                :current-page="currentPage"
                                :per-page="perPage"
                            >
                                <template slot="client_name" scope="row">
                                    <a :href="row.item.profile_url" target="_blank">{{ row.item.client_name }}</a>
                                </template>
                                <template slot="client_status" scope="row">
                                    {{ row.item.client_status ? 'Active' : 'Inactive' }}
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
            </b-col>
        </b-row>
    </div>
</template>

<script>
    import FormatsNumbers from "../../../mixins/FormatsNumbers";
    import FormatsDates from "../../../mixins/FormatsDates";
    import BusinessLocationFormGroup from "../BusinessLocationFormGroup";
    import {mapGetters} from "vuex";

    export default {
        mixins: [FormatsDates, FormatsNumbers],
        components: { BusinessLocationFormGroup },
        props: {
            servicesCoordinators: {
                type: Array,
                required: true,
            },
        },

        computed: {
            ...mapGetters({
                clientsLoaded: 'filters/isClientsLoaded',
                clientList: 'filters/clientList',
            }),
            clients() {
                return this.clientList.filter(x => {
                    if (! this.showInactiveClients) {
                        return x.active == 1;
                    }

                    return true;
                });
            },
        },

        data() {
            return {
                items: [],
                showInactiveClients: true,
                filters: new Form({
                    businesses: '',
                    services_coordinator_id: '',
                    client_id: '',
                    client_status: '',
                    days_since_contact: '',
                    json: 1,
                }),
                totalRows: 0,
                perPage: 15,
                currentPage: 1,
                fields: {
                    office_location: { sortable: true },
                    services_coordinator: { sortable: true },
                    client_name: { label: 'Client', sortable: true },
                    client_status: { sortable: true },
                    days_since_contact: { label: 'Days since last contact (last note from call center)', sortable: true },
                },
            };
        },

        methods: {
            async fetch() {
                this.filters.get(`/business/reports/services-coordinator`)
                .then( ({ data }) => {
                    this.items = data;
                    this.totalRows = this.items.length;
                    this.currentPage = 1;
                })
                .catch(() => {

                });
            },
        },

        async mounted() {
            await this.$store.dispatch('filters/fetchResources', ['clients']);
        },
    }
</script>
