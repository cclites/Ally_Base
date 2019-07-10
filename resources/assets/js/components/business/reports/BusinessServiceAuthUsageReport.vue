<template>
    <b-row>
        <b-col>
            <b-card header="Service Authorizations Usage Report"
                header-text-variant="white"
                header-bg-variant="info"
            >
                <div class="form-inline mb-3">
                    <b-col lg="3">
                        <b-form-group label="Effective During Dates:" class="form-inline">
                            <date-picker ref="startDate"
                                         style="max-width: 8rem;"
                                         v-model="form.start_date"
                                         placeholder="Start Date">
                            </date-picker> &nbsp;to&nbsp;
                            <date-picker ref="endDate"
                                         style="max-width: 8rem;"
                                         v-model="form.end_date"
                                         placeholder="End Date">
                            </date-picker>
                        </b-form-group>
                    </b-col>
                    <b-col lg="3">
                        <b-form-group label="For Office Location:">
                            <business-location-form-group
                                v-model="form.businesses"
                                :label="null"
                                class="mr-1 mt-1"
                                :allow-all="true"
                            />
                        </b-form-group>
                    </b-col>

                    <b-col lg="3">
                        <b-form-group label="For Client:">
                            <b-form-select v-model="form.client_id" class="mr-1 mt-1" :disabled="loading || loadingClients">
                                <option value="">-- Select a Client --</option>
                                <option v-for="item in clients" :key="item.id" :value="item.id">
                                    {{ item.nameLastFirst }}
                                </option>
                            </b-form-select>
                        </b-form-group>
                    </b-col>

                    <b-col lg="3">
                        <b-button @click="fetch()" variant="info" :disabled="busy">
                            <i class="fa fa-circle-o-notch fa-spin mr-1" v-if="busy"></i>
                            Generate Report
                        </b-button>
                        <b-button @click="print()" :disabled="busy"><i class="fa fa-print mr-1"></i>Print</b-button>
                    </b-col>
                </div>

                <b-row>
                    <b-col>
                        <loading-card v-if="busy" />
                        <div v-else class="table-responsive">
                            <table class="table" id="auths-table">
                                <thead>
                                    <th>Auth ID</th>
                                    <th>Auth Begin</th>
                                    <th>Auth End</th>
                                    <th>Service</th>
                                    <th>Unit Type</th>
    <!--                                <th>Payer</th>-->
                                    <th style="width: 200px">Code</th>
                                    <th style="width: 250px">Notes</th>
                                    <th>Days Until End</th>
                                </thead>
                                <tbody v-for="client in items" :key="client.id" class="mb-3">
                                    <tr>
                                        <td colspan="9">
                                            <strong><a :href="`/business/clients/${client.id}#insurance_service_auth`" target="_blank">{{ client.name }}</a></strong>
                                        </td>
                                    </tr>
                                    <tr v-for="auth in client.authorizations" :key="auth.id" class="striped">
                                        <td>{{ auth.id }}</td>
                                        <td>{{ formatDate(auth.effective_start) }}</td>
                                        <td>{{ formatDate(auth.effective_end) }}</td>
                                        <td>{{ auth.service ? auth.service.name : '-' }}</td>
                                        <td>{{ formatUnitType(auth.unit_type) }}</td>
    <!--                                    <td>{{ auth.payer_id }}</td>-->
                                        <td>{{ auth.service_auth_id }}</td>
                                        <td>{{ auth.notes }}</td>
                                        <td>{{ auth.days_until_end }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="9">
                                            Total Authorizations Ending for {{ client.name }}: {{ client.total }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <div v-if="!busy && items.length == 0" class="text-center mb-3 mt-4 text-muted">
                                {{ emptyText }}
                            </div>
                        </div>
                    </b-col>
                </b-row>
            </b-card>
        </b-col>
    </b-row>
</template>

<script>
    import BusinessLocationFormGroup from '../../../components/business/BusinessLocationFormGroup';
    import FormatsNumbers from '../../../mixins/FormatsNumbers';
    import FormatsDates from '../../../mixins/FormatsDates';
    import FormatsStrings from "../../../mixins/FormatsStrings";

    export default {
        components: { BusinessLocationFormGroup },
        mixins: [FormatsNumbers, FormatsDates, FormatsStrings],

        computed: {
            emptyText() {
                if (! this.hasRun) {
                    return 'Press Generate Report';
                }
                return 'No matching records available.';
            }
        },

        data() {
            return {
                loading: false,
                clients: [],
                payers: [],
                form: new Form({
                    businesses: '',
                    start_date: moment().startOf('isoweek').format('MM/DD/YYYY'),
                    end_date: moment().startOf('isoweek').add(6, 'days').format('MM/DD/YYYY'),
                    client_id: '',
                    days: 30,
                    json: 1,
                }),
                busy: false,
                items: [],
                hasRun: false,
                loadingClients: false,
            }
        },

        methods: {
            fetch() {
                if (! this.form.client_id) {
                    alert('You must select a client first.');
                    return;
                }

                this.busy = true;
                this.form.get('/business/reports/service-auth-usage')
                    .then( ({ data }) => {
                        this.items = data;
                    })
                    .catch(e => {})
                    .finally(() => {
                        this.busy = false;
                        this.hasRun = true;
                    })
            },

            print() {
                $("#auths-table").print();
            },

            async loadClients() {
                this.loadingClients = true;
                this.clients = [];
                await axios.get(`/business/reports/service-auth-usage/clients?json=1&businesses=${this.form.businesses}`)
                    .then( ({ data }) => {
                        this.clients = data.data;
                    })
                    .catch(() => {})
                    .finally(() => {
                        this.loadingClients = false;
                    });
            },

            formatUnitType(val) {
                if (val === '15m') {
                    return '15-min';
                }
                return this.stringFormat(val);
            },
        },

        async mounted() {
            this.loading = true;
            await this.loadClients();
            this.loading = false;
        },

        watch: {
            async 'form.businesses'(newValue, oldValue) {
                if (newValue != oldValue) {
                    await this.loadClients();
                }
            }
        },
    }
</script>

<style scoped>
    .table tbody {
        border-top: none!important;
    }
    .table tbody tr.striped:nth-of-type(odd) {
        background: #fff;
    }
    .table tbody tr.striped:nth-of-type(even) {
        background: #f2f4f8;
    }
</style>