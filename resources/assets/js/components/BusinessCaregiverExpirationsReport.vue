<template>
    <b-card>
        <b-row class="mb-2">
            <b-col md="3">
                <business-location-form-group
                        v-model="form.businesses"
                        label="Office Location"
                        :allow-all="true"
                />
            </b-col>
            <b-col lg="3">
                <b-form-group label="Caregiver">
                    <b-form-select v-model=" form.caregiver_id " :disabled=" form.show_scheduled ">
                        <option value="">All</option>
                        <option v-for="caregiver in caregivers" :value="caregiver.id" :key="caregiver.id">{{ caregiver.name }}</option>
                        <option disabled value="scheduled">-- Only Scheduled --</option>
                    </b-form-select>
                </b-form-group>
            </b-col>
            <b-col lg="3">
                <b-form-group label="Caregiver Status">
                    <b-form-select v-model="form.active">
                        <option value="">All</option>
                        <option :value="1">Active</option>
                        <option :value="0">Inactive</option>
                    </b-form-select>
                </b-form-group>
            </b-col>
            <b-col lg="3">
                <b-form-group label="Expiration Name">

                    <b-form-select v-model=" form.expiration_type ">
                        <option value="">-- Select A Type --</option>
                        <option :value=" exp.id " v-for=" ( exp, i ) in expirationtypes " :key=" i ">{{ exp.type }}</option>
                    </b-form-select>
                </b-form-group>
            </b-col>
            <b-col lg="6" class="d-block align-items-center d-sm-flex">
                <date-picker
                    :disabled=" selectingPast "
                    v-model=" form.start_date "
                    placeholder="Start Date"
                >
                </date-picker> &nbsp;to&nbsp;
                <date-picker
                    :disabled=" selectingPast "
                    v-model=" form.end_date "
                    placeholder="End Date"
                >
                </date-picker>
            </b-col>

            <b-col lg="12" class="d-flex mt-2">
                <b-form-checkbox class="m-0 vertical-center" @change=" showPast() ">Show already expired Licenses</b-form-checkbox>
            </b-col>
            <b-col lg="12" class="d-flex ">
                <b-form-checkbox class="m-0 vertical-center" @change=" showScheduled() ">Show scheduled caregivers</b-form-checkbox>
            </b-col>

            <b-col md="12" class="text-right">
                <b-form-group label="&nbsp;">
                    <b-button-group>
                        <b-button @click="generate()" variant="info" :disabled="loading"><i class="fa fa-file-pdf-o mr-1"></i>Generate Report</b-button>
                    </b-button-group>
                </b-form-group>
            </b-col>
        </b-row>

        <div class="table-responsive">
            <b-table bordered striped hover show-empty
                :items="items"
                :fields="fields"
                :current-page="currentPage"
                :per-page="perPage"
                :sort-by.sync="sortBy"
                :sort-desc.sync="sortDesc"
                :busy="loading"
            >
                <template slot="countdown" scope="row">
                    {{ row.item.expiration_date ? getCountdown( row.item.expiration_date ) : '-' }}
                </template>
                <template slot="actions" scope="row">

                    <b-row>

                        <b-col class="d-flex align-items-center flex-wrap">

                            <b-btn style="flex:1;" class="m-1" size="sm" :href="'/business/caregivers/' + row.item.caregiver_id + '#licenses' ">View Caregiver</b-btn>
                            <b-btn style="flex:1;" class="m-1" size="sm" @click="sendEmailReminder(row.item)" :disabled="row.item.sendingEmail">
                                <i class="fa fa-spinner fa-spin" v-if="row.item.sendingEmail"></i>
                                <i class="fa fa-envelope" v-else></i>
                                Email Reminder
                            </b-btn>
                        </b-col>
                    </b-row>
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
    import FormatsDates from '../mixins/FormatsDates';
    import BusinessLocationFormGroup from "../components/business/BusinessLocationFormGroup";

    export default {
        props: {
            certifications: {
                type: Array,
                default: () => [],
            },
            caregivers: {
                type: Array,
                default: () => [],
            },
            expirationtypes: {
                type: Array,
                default: () => []
            }
        },

        mixins: [FormatsDates],
        components: {BusinessLocationFormGroup},

        mounted() {
            this.totalRows = this.items.length;
        },

        data() {
            return {

                selectingPast : false,
                form: new Form({

                    start_date: moment().startOf('isoweek').subtract(7, 'days').format('MM/DD/YYYY'),
                    end_date: moment().add( 30, 'days' ).format('MM/DD/YYYY'),
                    caregiver_id: '',
                    show_expired: false,
                    active: '',
                    expiration_type: '',
                    businesses: '',
                    json: 1,
                    show_scheduled : false,
                }),
                totalRows: 0,
                perPage: 50,
                currentPage: 1,
                sortBy: null,
                sortDesc: false,
                loading: false,
                items: [],
                fields: [
                    {
                        key: 'caregiver_name',
                        label: 'Caregiver',
                        sortable: true,
                    },
                    {
                        key: 'name',
                        label: 'Expiration Name',
                        sortable: true,
                    },
                    {
                        key: 'expiration_date',
                        label: 'Expiration Date',
                        sortable: true,
                        formatter: (value) => this.formatDate(value, 'MMM D YYYY'),
                    },
                    {
                        key: 'countdown',
                        label: 'Time Until Expiration',
                        sortable: true,
                    },
                    {
                        key: 'actions',
                        class: 'hidden-print'
                    }
                ]
            }
        },

        methods: {

            showPast(){

                this.selectingPast = !this.selectingPast;

                if( this.selectingPast ){

                    this.form.start_date = '01/01/1900';
                    this.form.end_date = moment().format('MM/DD/YYYY');
                } else {

                    this.form.start_date = moment().startOf('isoweek').subtract(7, 'days').format('MM/DD/YYYY');
                    this.form.end_date = moment().add( 30, 'days' ).format('MM/DD/YYYY');
                }
            },
            showScheduled(){

                this.form.show_scheduled = !this.form.show_scheduled;

                if( this.form.show_scheduled ) this.form.caregiver_id = 'scheduled';
                else this.form.caregiver_id = '';
            },
            sendEmailReminder(item) {
                if (item.sendingEmail) {
                    return;
                }
                item.sendingEmail = true;

                let form = new Form({});
                form.post(`/business/caregivers/licenses/${item.id}/send-reminder`)
                    .then(() => {})
                    .catch(() => {})
                    .finally(() => {
                        item.sendingEmail = false;
                    })
            },

            getCountdown(date) {
                if(moment().isSameOrAfter(date)) {
                    return 'Already Expired';
                }

                return moment(date).toNow(true);
            },

            generate() {
                this.loading = true;
                this.form.get( '/business/reports/caregiver-expirations' )
                    .then( response => {

                        this.items     = response.data;
                        this.totalRows = this.items.length;
                    })
                    .catch(() => {})
                    .finally(() => {

                        this.loading = false;
                    });
            },
        }
    }
</script>

<style scoped>
    input.days {
        width: 70px;
    }
    .vertical-center {
        display: flex;
        align-items: center;
    }
</style>
