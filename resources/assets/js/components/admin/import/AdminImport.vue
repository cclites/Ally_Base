<template>
    <div>
        <b-card>
            <admin-import-form :businesses="businesses"
                               @imported="loadImportedData"
                               v-show="imported.length === 0"
            ></admin-import-form>

            <table v-if="imported.length > 0" class="table table-bordered">
                <thead>
                <tr>
                    <th>Clock In</th>
                    <th>Clock Out</th>
                    <th>Duration</th>
                    <th colspan="2">Client</th>
                    <th colspan="2">Caregiver</th>
                    <th>CG Rate</th>
                    <th>Reg. Fee</th>
                    <th>Mileage</th>
                    <th>Other Expenses</th>
                </tr>
                </thead>
                <tbody>
                    <admin-import-id-row v-for="row in imported"
                                         :clients="clients"
                                         :caregivers="caregivers"
                                         :shift.sync="row.shift"
                                         :identifiers="row.identifiers"
                    ></admin-import-id-row>
                </tbody>
            </table>


        </b-card>
    </div>
</template>

<script>
    export default {

        components: {
            'admin-import-form': require('./AdminImportForm'),
            'admin-import-id-row': require('./AdminImportIdRow'),
        },

        props: {},

        data() {
            return {
                'businesses': [],
                'caregivers': [],
                'clients': [],
                'imported': [],
            }
        },

        mounted() {
            this.loadBusinesses();
            this.loadCaregivers();
            this.loadClients();
        },

        methods: {
            loadBusinesses() {
                axios.get('/admin/businesses').then(response => this.businesses = response.data);
            },

            loadClients() {
                axios.get('/admin/clients?json=1').then(response => this.clients = response.data);
            },

            loadCaregivers() {
                axios.get('/admin/caregivers?json=1').then(response => this.caregivers = response.data);
            },

            loadImportedData(data) {
                this.imported = data;
            }
        },
    }
</script>
