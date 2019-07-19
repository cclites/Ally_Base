<template>
    <b-card header="Payroll Summary"
            header-text-variant="white"
            header-bg-variant="info"
            class="mb-3"
    >
        <div class="form-inline">
            <business-location-form-group
                    v-model="form.business"
                    :allow-all="true"
                    class="mb-2 mr-2"
                    :label="null"
            />
            <date-picker v-model="form.start"
                         placeholder="Start Date"
                         weekStart="1"
                         class="mb-2 mr-2 col-md-2"
            >
            </date-picker>
            &nbsp;to&nbsp;
            <date-picker v-model="form.end"
                         placeholder="End Date"
                         class="mb-2 mr-2 col-md-2">
            </date-picker>
            <b-select v-model="form.caregiver" class="mb-2 mr-2">
                <option value="">All Caregivers</option>
                <option v-for="caregiver in caregivers" :key="caregiver.id" :value="caregiver.id">{{ caregiver.nameLastFirst }}</option>
            </b-select>

            <b-select v-model="form.client_type" class="mb-2 mr-2">
                <option v-for="type in clientTypes" :key="type.value" :value="type.value">{{ type.text }}</option>
            </b-select>

            <b-button @click="fetch()" variant="info" :disabled="busy" class="mr-2 mb-2">
                <i class="fa fa-circle-o-notch fa-spin mr-1" v-if="busy"></i>
                Generate Report
            </b-button>
        </div>

        <div class="table-responsive">
            <b-table bordered striped hover show-empty
                     :items="items"
                     :fields="fields"
                     :current-page="currentPage"
                     :per-page="perPage"
                     :sort-by.sync="sortBy"
                     :sort-desc.sync="sortDesc"
                     :footClone="footClone"
            >
                <template slot="FOOT_location" scope="item" class="primary">
                    &nbsp;
                </template>

                <template slot="FOOT_date" scope="item">
                    &nbsp;<strong>For Location:</strong> {{ totals.location }}
                </template>

                <template slot="FOOT_caregiver" scope="item" class="primary">
                    &nbsp;<strong>For Client Types: </strong> {{totals.type}}
                </template>

                <template slot="FOOT_type" scope="item">
                    &nbsp;
                </template>

                <template slot="FOOT_name" scope="item">
                    &nbsp;
                </template>

                <template slot="FOOT_amount" scope="item" class="primary">
                    &nbsp;<strong>Total:  </strong> {{ moneyFormat(totals.amount ) }}
                </template>

            </b-table>
        </div>
    </b-card>

</template>

<script>

    import BusinessLocationSelect from '../../business/BusinessLocationSelect';
    import BusinessLocationFormGroup from '../../business/BusinessLocationFormGroup';
    import FormatsNumbers from '../../../mixins/FormatsNumbers';
    import FormatsDates from '../../../mixins/FormatsDates';
    import Constants from '../../../mixins/Constants';

    export default {
        name: "payroll-summary-report",
        components: { BusinessLocationFormGroup, BusinessLocationSelect },
        mixins: [FormatsNumbers, FormatsDates, Constants],
        props: {

        },
        data() {
            return {
                items: [],
                form: new Form({
                    start: moment().startOf('isoweek').subtract(7, 'days').format('MM/DD/YYYY'),
                    end: moment().startOf('isoweek').subtract(1, 'days').format('MM/DD/YYYY'),
                    client_type: '',
                    business: '',
                    caregiver: '',
                    json: 1,
                    }
                ),
                busy: false,
                totalRows: 0,
                perPage: 30,
                currentPage: 1,
                sortBy: 'caregivers',
                sortDesc: false,
                caregivers: {
                    type: [Array, Object],
                    default: () => { return []; },
                },
                fields: [
                    //{ key: 'location', label: 'Location', sortable: true, },
                    { key: 'date', label: 'Date', sortable: true, formatter: x => this.formatDate(x)},
                    { key: 'caregiver', label: 'Caregiver', sortable: true, },
                    //{ key: 'type', label: 'Client Type', sortable: true },
                    { key: 'amount', label: 'Amount', sortable: true, formatter: x => this.moneyFormat(x) },
                ],
                totals: [],
                footClone: false
            }
        },
        methods: {

            fetch() {
                this.busy = true;
                this.form.get('/business/reports/payroll-summary-report')
                    .then( ({ data }) => {

                        this.items = data.data;
                        this.totals = data.totals;
                        this.totalRows = this.items.length;
                    })
                    .catch(e => {})
                    .finally(() => {
                        this.busy = false;
                        this.hasRun = true;
                        this.footClone = true;
                    })
            },

            fetchCaregivers(){

                this.loading = true;
                axios.get('/business/reports/payroll-summary-report/' + this.form.business)
                    .then( ({ data }) => {
                        this.caregivers = data;
                    })
                    .catch(e => {})
                    .finally(() => {
                        this.loading = false;
                    })
                this.loading = false;

            }
        },

        mounted(){
            this.fetchCaregivers();
        },
    }
</script>

<style scoped>
</style>