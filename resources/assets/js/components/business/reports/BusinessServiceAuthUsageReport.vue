<template>
    <b-row>
        <b-col>
            <b-card header="Service Authorization Usage Report"
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
                            <div v-for="auth in items" :key="auth.id" class="mb-4">
                                <div class="mb-2">
                                    <strong>
                                        {{ auth.service_name }} - {{ auth.service_code }}
                                        <span v-if="auth.name">
                                         (Auth: <a :href="`/business/clients/${auth.client_id}#insurance_service_auth`" target="_blank">{{ auth.name }}</a>)
                                        </span>
                                    </strong>
                                </div>
<!--                                <b-table bordered striped hover show-empty-->
<!--                                    :items="auth.periods"-->
<!--                                    :fields="fields"-->
<!--                                    sort-by="period"-->
<!--                                >-->
<!--                                </b-table>-->
                                <table class="table-fit-more table b-table table-striped table-hover table-bordered">
                                    <thead>
                                        <tr>
                                            <th scope="col" colspan="1"></th>
                                            <th scope="col" colspan="2" class="bl bt br text-center p-0">Confirmed</th>
                                            <th scope="col" colspan="2" class="bt br text-center p-0">Unconfirmed</th>
                                            <th scope="col" colspan="2" class="bt br text-center p-0">Scheduled</th>
                                            <th scope="col" colspan="2" class="bt br text-center p-0">Auth Limits</th>
                                            <th scope="col" colspan="2" class="bt br text-center p-0">Remaining</th>
                                        </tr>
                                        <tr>
                                            <th scope="col">Period</th>
                                            <!-- Confirmed -->
                                            <th scope="col" class="bl">Units</th>
                                            <th scope="col" class="br">Hours</th>
                                            <!-- Unconfirmed -->
                                            <th scope="col">Units</th>
                                            <th scope="col" class="br">Hours</th>
                                            <!-- Scheduled -->
                                            <th scope="col">Units</th>
                                            <th scope="col" class="br">Hours</th>
                                            <!-- Auth Limits -->
                                            <th scope="col">Units</th>
                                            <th scope="col" class="br">Hours</th>
                                            <!-- Remaining -->
                                            <th scope="col">Units</th>
                                            <th scope="col" class="br">Hours</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="item in auth.periods" :key="item.period_display" :class="item.is_exceeded ? 'table-danger' : ''">
                                            <!-- Period -->
                                            <td class="br">{{ item.period_display }}</td>
                                            <!-- Confirmed -->
                                            <td class="bl">{{ item.confirmed_units }}</td>
                                            <td class="br">{{ item.confirmed_hours }}</td>
                                            <!-- Unconfirmed -->
                                            <td class="bl">{{ item.unconfirmed_units }}</td>
                                            <td class="br">{{ item.unconfirmed_hours }}</td>
                                            <!-- Scheduled -->
                                            <td class="bl">{{ item.scheduled_units }}</td>
                                            <td class="br">{{ item.scheduled_hours }}</td>
                                            <!-- Auth Limits -->
                                            <td class="bl">{{ item.allowed_units }}</td>
                                            <td class="br">{{ item.allowed_hours }}</td>
                                            <!-- Remaining -->
                                            <td class="bl">{{ item.remaining_units }}</td>
                                            <td class="br">{{ item.remaining_hours }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
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
                    json: 1,
                }),
                busy: false,
                items: [],
                hasRun: false,
                loadingClients: false,
                fields: {
                    period_display: { label: 'Period', sortable: true },
                    // allowed_units: { label: '', sortable: true },
                    confirmed_shift_hours: { label: 'Confirmed', sortable: true },
                    // unconfirmed_shift_hours: { label: '', sortable: true },
                    scheduled_hours: { label: 'Scheduled', sortable: true },
                    allowed_hours: { label: 'Allowed (Hours)', sortable: true },
                    remaining_hours: { label: 'Remaining', sortable: true },
                },
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
    .bt { border-top: 1px solid #e9ecef!important; }
    .bl { border-left: 1px solid #e9ecef!important; }
    .br { border-right: 1px solid #e9ecef!important; }
    .bb { border-bottom: 1px solid #e9ecef!important; }
</style>
