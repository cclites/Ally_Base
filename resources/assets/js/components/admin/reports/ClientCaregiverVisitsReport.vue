<template>
    <b-row>
        <b-col>
            <b-card>
                <b-row class="mb-4">
                    <b-col lg="4">
                        <div class="d-flex pt-lg-4 mt-lg-2">
                            <date-picker
                                    class="mb-1"
                                    v-model="filter.startDate"
                                    placeholder="Start Date">
                            </date-picker>
                            <div class="mx-2 mt-2">to</div>
                            <date-picker
                                    class="mb-1"
                                    v-model="filter.endDate"
                                    placeholder="End Date">
                            </date-picker>
                        </div>
                    </b-col>
                    <b-col lg="3">
                        <b-form-group label="Client Filter">
                            <b-form-select v-model="filter.clientId" class="mr-1 mb-1">
                                <option value="">All Clients</option>
                                <option v-for="item in clientList" :value="item.id" :key="item.id">{{ item.name }}</option>
                            </b-form-select>
                        </b-form-group>
                    </b-col>
                    <b-col lg="3">
                        <b-form-group label="Caregiver Filter">
                            <b-form-select v-model="filter.caregiverId" class="mx-1 mb-1">
                                <option value="">All Caregivers</option>
                                <option v-for="item in caregiverList" :value="item.id" :key="item.id">{{ item.name }}</option>
                            </b-form-select>
                        </b-form-group>
                    </b-col>
                    <b-col lg="2" class="pt-lg-4">
                        <b-button 
                            type="submit" 
                            @click.prevent="fetchData"
                            variant="info"
                            class="mt-lg-2"
                        >
                            Search
                        </b-button>
                    </b-col>
                </b-row>

                <loading-card v-show="loading"></loading-card>

                <b-table v-show="! loading" show-empty :items="items" :fields="fields"></b-table>
            </b-card>
        </b-col>
    </b-row>
</template>

<script>
    export default {

        props: ['clients', 'caregivers', 'startDate', 'endDate'],

        data() {
            return {
                filter: {
                    clientId: '',
                    caregiverId: '',
                    startDate: this.startDate,
                    endDate: this.endDate
                },
                loading: false,
                items: [],
                fields: [
                    {
                        label: 'Client',
                        key: 'client',
                        sortable: true
                    },
                    {
                        label: 'Caregiver',
                        key: 'caregiver',
                        sortable: true
                    },
                    {
                        label: 'Visits',
                        key: 'shift_count',
                        sortable: true
                    }
                ]
            }
        },

        created() {
        },

        methods: {
            fetchData() {
                this.loading = true;
                axios.post('/admin/reports/client-caregiver-visits', this.filter)
                    .then(response => {
                        this.items = response.data.table_data;
                        this.filter.startDate = response.data.range[0];
                        this.filter.endDate = response.data.range[1];
                        this.loading = false;
                    }).catch(error => {
                        this.loading = false;
                        console.error(error.response);
                    });
            }
        },

        computed: {
            clientList() {
                return _.sortBy(this.clients, 'name');
            },

            caregiverList() {
                return _.sortBy(this.caregivers, 'name');
            }
        }
    }
</script>