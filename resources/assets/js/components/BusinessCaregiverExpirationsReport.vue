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
                <b-form-checkbox class="m-0 vertical-center" @change=" showPast() ">Show only expired expirations ( will ignore date range )</b-form-checkbox>
            </b-col>
            <b-col lg="12" class="d-flex mt-1">
                <b-form-checkbox class="m-0 vertical-center" @change=" showScheduled() ">Show only caregivers with future schedules</b-form-checkbox>
            </b-col>

            <b-col class="mt-2 d-flex align-items-stretch align-items-sm-center justify-content-end flex-column flex-sm-row">

                <b-btn @click="exportExcel()" variant="success" class="m-1"><i class="fa fa-file-excel-o mr-2"></i>Export to Excel</b-btn>
                <b-button v-b-modal.deficiency-letters-button variant="primary" class="m-1" :disabled="loading"><i class="fa fa-file-pdf-o mr-2"></i>Generate Deficiency Letters</b-button>
                <b-button @click="generate()" variant="info" class="m-1" :disabled="loading"><i class="fa fa-file-o mr-2"></i>Generate Report</b-button>
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

        <b-modal size="xl" id="deficiency-letters-button" title="Deficiency Letters Template" @ok=" createDeficiencyLetter() " ok-variant="primary" ok-title="Generate Deficiency Letters">

            <b-row class="flex-column">

                <b-col style="flex:1">

                    <p>#Caregiver-Address-Block#<br/> 123 example road, <br/><br/> City, State, Zipcode</p>
                </b-col>

                <b-col style="flex:1">

                    <p>Dear #Caregiver-First-Name#,</p>
                </b-col>

                <b-col style="flex:1">

                    <b-form-textarea
                        rows="3"
                        v-model=" form.intro_paragraph "
                    ></b-form-textarea>
                </b-col>

                <b-col style="flex:1">

                    <b-form-textarea
                        rows="4"
                        v-model=" form.middle_paragraph "
                    ></b-form-textarea>
                </b-col>

                <b-col style="flex:1" class="mt-2">

                    <p class="mb-3">#Expiration-Table#</p>
                    <p>Audited on #Today#. Includes items expiring on #Date-Range-Start# through #Date-Range-End#.</p>
                </b-col>

                <b-col style="flex:1">

                    <b-form-textarea
                        rows="4"
                        v-model=" form.outro_paragraph "
                    ></b-form-textarea>
                </b-col>

                <b-col style="flex:1">

                    <b-form-input
                        id="farewell-input"
                        v-model=" form.final_words "
                    ></b-form-input>
                </b-col>

                <b-col style="flex:1" class="my-4">

                    <label for="farewell-input">Sincerely,</label>
                    <b-form-input
                        id="farewell-input"
                        v-model=" form.farewell "
                    ></b-form-input>
                </b-col>

                <b-col style="flex:1">

                    <h3>Explaination of Variables <small class="text-muted">be sure to replace the values denoted by 'XXXXX'</small></h3>
                    <p>#Caregiver-Address-Block# - Full name and address of caregiver.</p>
                    <p>#Caregiver-First-Name# - Caregiver first name.</p>
                    <p>#Today# - Today's date.</p>
                    <p>#Date-Range-Start# - Results selected start date.</p>
                    <p>#Date-Range-End# - Results selected end date.</p>
                    <p>#Expiration-Table# - List of expirations in a table format</p>
                </b-col>
            </b-row>
        </b-modal>
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

                    start_date          : moment().startOf('isoweek').subtract(7, 'days').format('MM/DD/YYYY'),
                    end_date            : moment().add( 30, 'days' ).format('MM/DD/YYYY'),
                    caregiver_id        : '',
                    show_expired        : false,
                    active              : '',
                    expiration_type     : '',
                    businesses          : '',
                    json                : 1,
                    show_scheduled      : false,
                    export              : 0,

                    deficiency_letter   : 0,

                    intro_paragraph  : 'Recently, we performed a routine audit of all our Independent Caregiver folders. The following listed items have expired or will be expiring soon.',
                    middle_paragraph : 'In accordance with State Regulations, we need you to provide copies of these documents. We are requesting that you return the documents within fourteen (14) days of the date of this letter. Please provide these documents by email to XXXXXXXXXXXXX or fax to XXXXXXXXXXXXX or mail to XXXXXXXXXXXX or come in person to our office.',
                    outro_paragraph  : 'As per XXXXXXXXX State Statute XXXXXXXX, when a deficiency in credentials comes to the attention of the nurse registry, the nurse registry shall advise the client to terminate the referred caregiver.  Furthermore, we will not be able to continue to refer you to new clients unless all required documents in your folder are current.',
                    final_words      : 'Please comply with this request so you can continue to serve your clients with their home care needs.',
                    farewell         : '',
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

            createDeficiencyLetter(){

                this.form.deficiency_letter = 1;
                window.open( this.form.toQueryString( `/business/reports/caregiver-expirations` ) );
                this.form.deficiency_letter = 0;
            },
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
            exportExcel() {

                this.form.export = 1;
                window.location = this.form.toQueryString( `/business/reports/caregiver-expirations` );
                this.form.export = 0;
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
