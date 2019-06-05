<template>
    <div>
        <b-row class="mb-2">
            <b-col lg="12">
                <b-btn variant="info" @click="addAuth()">Add Authorization</b-btn>
            </b-col>
        </b-row>
        <div>
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
                    <template slot="units" scope="row">
                        <span v-if="row.item.period == 'specific_days'">N/A</span>
                        <span v-else>{{ row.item.units }}</span>
                    </template>
                    <template slot="actions" scope="row">
                        <!-- We use click.stop here to prevent a 'row-clicked' event from also happening -->
                        <b-btn size="sm" @click="editAuth(row.item.id)">
                            <i class="fa fa-edit"></i>
                        </b-btn>
                        <b-btn size="sm" @click="deleteAuth(row.item.id)">
                            <i class="fa fa-trash"></i>
                        </b-btn>
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

        <form @submit.prevent="submitForm()" @keydown="form.clearError($event.target.name)">
            <b-modal id="filterColumnsModal" :title="title" v-model="showAuthModal" size="lg">
                <b-container fluid>
                    <b-row>
                        <b-col lg="4">
                            <b-form-group label="Service Auth ID" label-class="required">
                                <b-form-input v-model="form.service_auth_id" type="text" max="255"></b-form-input>
                            </b-form-group>
                        </b-col>
                        <b-col lg="6">
                            <b-row>
                                <b-col lg="6">
                                    <b-form-group label="Service Code" label-class="required">
                                        <b-form-select v-model="form.service_id" class="mr-1 mb-1" name="report_type">
                                            <option :value="null">--Select--</option>
                                            <option v-for="s in services" :value="s.id" :key="s.id">{{ s.code}}</option>
                                        </b-form-select>
                                    </b-form-group>
                                </b-col>
                                <b-col lg="6">
                                    <b-form-group label="Service Type">
                                        <b-form-input type="text" :value="selectedServiceName" plaintext></b-form-input>
                                    </b-form-group>
                                </b-col>
                            </b-row>
                        </b-col>
                    </b-row>
                    <b-row>
                        <b-col lg="6">
                            <b-form-group label="Effective Start" label-class="required">
                                <mask-input v-model="form.effective_start" type="date" class="date-input"></mask-input>
                            </b-form-group>
                        </b-col>
                        <b-col lg="6">
                            <b-form-group label="Effective End" label-class="required">
                                <mask-input v-model="form.effective_end" type="date" class="date-input"></mask-input>
                            </b-form-group>
                        </b-col>
                    </b-row>
                    <b-row>
                        <b-col lg="3">
                            <b-form-group label="Units" label-class="required">
                                <b-form-input type="number" step="any" v-model="form.units" :disabled="form.period == 'specific_days'" />
                            </b-form-group>
                        </b-col>
                        <b-col lg="3">
                            <b-form-group label="Unit Type" label-class="required">
                                <b-form-select v-model="form.unit_type" class="mr-1 mb-1">
                                    <option value="15m">15 Minutes</option>
                                    <option value="hourly">Hourly</option>
                                    <option value="fixed">Fixed</option>
                                </b-form-select>
                            </b-form-group>
                        </b-col>
                        <b-col lg="3">
                            <b-form-group label="Period" label-class="required">
                                <b-form-select v-model="form.period" class="mr-1 mb-1">
                                    <option value="daily">Daily</option>
                                    <option value="weekly">Weekly</option>
                                    <option value="monthly">Monthly</option>
                                    <option value="term">Term</option>
                                    <option value="specific_days">Specific Days of Week</option>
                                </b-form-select>
                            </b-form-group>
                        </b-col>
                        <b-col lg="3">
                            <b-form-group label="# of Occurrences">
                                <b-form-input
                                    type="number"
                                    step="any"
                                    min="1"
                                    v-model="form.occurrences"
                                    :disabled="['specific_days', 'term'].includes(form.period)"
                                />
                            </b-form-group>
                        </b-col>
                    </b-row>
                    <b-row v-if="form.period == 'specific_days'">
                        <b-col lg="12">
                            <div class="d-flex days-row">
                                <div>
                                    <b-form-group label="Day">
                                        <b-form-input value="Units" type="text" plaintext></b-form-input>
                                    </b-form-group>
                                </div>
                                <div>
                                    <b-form-group label="Sun">
                                        <b-form-input v-model="form.sunday" type="number" step="any"></b-form-input>
                                    </b-form-group>
                                </div>
                                <div>
                                    <b-form-group label="Mon">
                                        <b-form-input v-model="form.monday" type="number" step="any"></b-form-input>
                                    </b-form-group>
                                </div>
                                <div>
                                    <b-form-group label="Tues">
                                        <b-form-input v-model="form.tuesday" type="number" step="any"></b-form-input>
                                    </b-form-group>
                                </div>
                                <div>
                                    <b-form-group label="Wed">
                                        <b-form-input v-model="form.wednesday" type="number" step="any"></b-form-input>
                                    </b-form-group>
                                </div>
                                <div>
                                    <b-form-group label="Thurs">
                                        <b-form-input v-model="form.thursday" type="number" step="any"></b-form-input>
                                    </b-form-group>
                                </div>
                                <div>
                                    <b-form-group label="Fri">
                                        <b-form-input v-model="form.friday" type="number" step="any"></b-form-input>
                                    </b-form-group>
                                </div>
                                <div>
                                    <b-form-group label="Sat">
                                        <b-form-input v-model="form.saturday" type="number" step="any"></b-form-input>
                                    </b-form-group>
                                </div>
                            </div>
                        </b-col>
                    </b-row>
                    <b-row>
                        <b-col lg="12">
                            <b-form-group label="Notes">
                                <b-form-textarea :rows="2" v-model="form.notes"></b-form-textarea>
                            </b-form-group>
                        </b-col>
                    </b-row>
                </b-container>
                <div slot="modal-footer">
                    <b-button variant="success"
                            type="submit"
                            :disabled="loading"
                    >
                        {{ buttonText }}
                    </b-button>
                    <b-btn variant="default" @click="showAuthModal=false">Close</b-btn>
                </div>
            </b-modal>
        </form>
    </div>
</template>

<script>
    import FormatsStrings from "../../../mixins/FormatsStrings";

    export default {
        mixins: [FormatsStrings],

        props: ['auths', 'services', 'clientId'],

        data() {
            return {
                items: [],
                auth: {},
                showAuthModal: false,
                totalRows: 0,
                perPage: 15,
                currentPage: 1,
                sortBy: 'service_type',
                sortDesc: false,
                filter: null,
                fields: [
                    {
                        key: 'service_type',
                        label: 'Service Type',
                        sortable: true,
                        formatter: x => x ? x : '-',
                    },
                    {
                        key: 'service_code',
                        label: 'Service Code',
                        sortable: true,
                        formatter: x => x ? x : '-',
                    },
                    {
                        key: 'effective_start',
                        label: 'Start',
                        sortable: true,
                    },
                    {
                        key: 'effective_end',
                        label: 'End',
                        sortable: true,
                    },
                    {
                        key: 'units',
                        label: 'Units',
                        sortable: true,
                    },
                    {
                        key: 'unit_type',
                        label: 'Unit Type',
                        sortable: true,
                        formatter: (val) => {
                            if (val === '15m') return '15 Minutes';
                            return this.stringFormat(val);
                        }
                    },
                    {
                        key: 'period',
                        label: 'Period',
                        sortable: true,
                        formatter: (val) => this.stringFormat(val),
                    },
                    {
                        key: 'notes',
                        label: 'Notes',
                        sortable: true,
                    },
                    {
                        key: 'actions',
                        class: 'hidden-print'
                    }
                ],
                form: this.makeForm(this.auth),
                loading: false,
                calculatingOccurrences: false,
            }
        },

        mounted() {
            this.items = Object.keys(this.auths).map(x => this.auths[x]);
        },

        computed: {
            title() {
                return (this.auth.id) ? 'Edit Authorization' : 'Add New Authorization';
            },
            buttonText() {
                return (this.auth.id) ? 'Save' : 'Create';
            },
            selectedServiceName() {
                let service = this.services.find(x => x.id === this.form.service_id);
                if (service) {
                    return service.name;
                }
                return '';
            },
        },

        watch: {
            'form.period'(newValue, oldValue) {
                this.setOccurrences();
            },
            'form.effective_end'(newValue, oldValue) {
                this.setOccurrences();
            },
            'form.effective_start'(newValue, oldValue) {
                this.setOccurrences();
            },
            'form.occurrences'(newValue, oldValue) {
                if (this.calculatingOccurrences) {
                    return;
                }
                this.calculateEndDateFromOccurrences();
            },
        },
        methods: {
            setOccurrences() {
                this.calculatingOccurrences = true;
                this.form.occurrences = this.getOccurrencesFromEndDate();
                console.log('occurrences set to', this.form.occurrences);
                this.$nextTick(() => {
                    this.calculatingOccurrences = false;
                });
            },

            getOccurrencesFromEndDate() {
                console.log('getOccurrencesFromEndDate');
                if (! ['daily', 'weekly', 'monthly'].includes(this.form.period)) {
                    console.log('clearing occurrences');
                    return '';
                }

                let start = moment(this.form.effective_start);
                let end = moment(this.form.effective_end);

                let between = moment.duration(end.diff(start));

                console.log('raw diff between dates: ', between);
                switch (this.form.period)
                {
                    case 'daily':
                        console.log('days: ', between.as('days'));
                        return between.as('days');
                    case 'weekly':
                        console.log('weeks: ', between.as('weeks'));
                        return between.as('weeks');
                    case 'monthly':
                        console.log('months: ', between.as('months'));
                        return between.as('months');
                    default:
                        return;
                }
            },

            calculateEndDateFromOccurrences() {
                if (! this.form.effective_start || ! this.form.occurrences) {
                    console.log('cant calculate end date.');
                    return;
                }
                console.log('calculating end date');

                let endDate = moment(this.form.effective_start);
                switch (this.form.period)
                {
                    case 'daily':
                        endDate.add(this.form.occurrences, 'days');
                        break;
                    case 'weekly':
                        endDate.add(this.form.occurrences, 'weeks');
                        break;
                    case 'monthly':
                        endDate.add(this.form.occurrences, 'months');
                        break;
                    default:
                        return;
                }
                
                console.log('should change effective end', endDate);

                this.form.effective_end = endDate.format('MM/DD/YYYY');
            },

            authSaved(data) {
                let item = this.items.find(x => x.id === data.id);
                if (item) {
                    item = Object.assign(item, data);
                } else {
                    this.items.push(data);
                }
            },
            addAuth() {
                this.auth = {};
                this.form = this.makeForm(this.auth);
                this.setOccurrences();
                this.showAuthModal = true;
            },
            editAuth(id) {
                this.auth = this.items.find(x => x.id == id);
                this.form = this.makeForm(this.auth);
                this.setOccurrences();
                this.showAuthModal = true;
            },
            deleteAuth(id) {
                if (confirm("Are you sure you wish to delete this authorization?")) {
                    let form = new Form();
                    form.submit('delete', `/business/authorization/${id}`)
                        .then( ({ data }) => {
                            this.items = this.items.filter(x => x.id !== id);
                        });
                }
            },
            onFiltered(filteredItems) {
                // Trigger pagination to update the number of buttons/pages due to filtering
                this.totalRows = filteredItems.length;
                this.currentPage = 1;
            },
            makeForm(defaults = {}) {
                return new Form({
                    service_auth_id: defaults.service_auth_id || '',
                    client_id: this.clientId,
                    service_id: defaults.service_id || null,
                    effective_start: defaults.effective_start || moment().format('MM/DD/YYYY'),
                    effective_end: defaults.effective_end || moment().add(1, 'years').format('MM/DD/YYYY'),
                    units: defaults.units || 0,
                    unit_type: defaults.unit_type || "hourly",
                    period: defaults.period || "weekly",
                    occurrences: '',
                    notes: defaults.notes || "",
                    sunday: defaults.sunday || 0,
                    monday: defaults.monday || 0,
                    tuesday: defaults.tuesday || 0,
                    wednesday: defaults.wednesday || 0,
                    thursday: defaults.thursday || 0,
                    friday: defaults.friday || 0,
                    saturday: defaults.saturday || 0,
                });
            },
            submitForm() {
                this.loading = true;
                let method = this.auth.id ? 'patch' : 'post';
                let url = this.auth.id ? `/business/authorization/${this.auth.id}` : '/business/authorization';
                this.form.submit(method, url)
                    .then(response => {
                        this.authSaved(response.data.data);
                        this.showAuthModal = false;
                    })
                    .finally(() => this.loading = false)
            },
        }
    }
</script>


<style lang="scss" scoped>
    .loader {
        border: 8px solid #f3f3f3;
        border-radius: 50%;
        border-top: 8px solid #3498db;
        width: 50px;
        height: 50px;
        -webkit-animation: spin-data-v-7012acc5 2s linear infinite;
        animation: spin-data-v-7012acc5 2s linear infinite;
        margin: 0 auto;
    }

    /* Safari */
    @-webkit-keyframes spin {
        0% { -webkit-transform: rotate(0deg); }
        100% { -webkit-transform: rotate(360deg); }
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .error-msg {
        margin-top: 7px;
        color: red;
    }

    .days-row div { padding-right: 1rem; padding-left: 1rem; }
</style>