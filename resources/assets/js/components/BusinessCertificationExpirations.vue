<template>
    <b-card>
        <b-row class="mb-2">
            <b-col md="2">
                <business-location-form-group
                        v-model="form.business_id"
                        label="Office Location"
                        :allow-all="true"
                />
            </b-col>
            <b-col lg="2">
                <b-form-group label="Caregiver">
                    <b-form-select v-model="form.caregiver_id">
                        <option value="">All</option>
                        <option v-for="caregiver in caregivers" :value="caregiver.id" :key="caregiver.id">{{ caregiver.name }}</option>
                    </b-form-select>
                </b-form-group>
            </b-col>
            <b-col lg="2">
                <b-form-group label="Caregiver Status">
                    <b-form-select v-model="form.active">
                        <option value="">All</option>
                        <option :value="1">Active</option>
                        <option :value="0">Inactive</option>
                    </b-form-select>
                </b-form-group>
            </b-col>
            <b-col lg="2">
                <b-form-group label="Expiration Name">
                    <b-form-input v-model="form.name" placeholder="Example: CNA" />
                </b-form-group>
            </b-col>
            <b-col lg="3">
                <b-form-group label="Show licenses expiring:">
                    <b-form-input
                        type="number"
                        v-model="form.days_range"
                        placeholder="Number of days"
                        class="days"
                        :min="0"
                        :max="999"
                        :disabled="form.show_expired"
                    />
                    <span class="ml-2">Days from today</span>
                </b-form-group>
            </b-col>

            <b-col lg="3" class="vertical-center">
                <b-form-checkbox v-model="form.show_expired">Show expired Licenses</b-form-checkbox>
            </b-col>
            <b-col md="12" class="text-right">
                <b-form-group label="&nbsp;">
                    <b-button-group>
                        <b-button @click="generateReport()" variant="info" :disabled="loading"><i class="fa fa-file-pdf-o mr-1"></i>Generate Report</b-button>
                        <!-- b-button @click="print()"><i class="fa fa-print mr-1"></i>Print</b-button -->
                    </b-button-group>
                </b-form-group>
            </b-col>

        </b-row>

        <div class="d-flex justify-content-center" v-if="loading">
            <div class="my-5">
                <i class="fa fa-spinner fa-spin fa-3x fa-fw"></i>
            </div>
        </div>
        <div v-else>
            <b-row>
                <b-col>
                    <b-table
                            class="certifications-table"
                            :items="certifications"
                            :fields="fields"
                            sort-by="expiration_date"
                            empty-text="No Results"
                            :busy="loading"
                    />
                </b-col>
            </b-row>
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
    import BusinessLocationSelect from "../components/business/BusinessLocationSelect";
    import BusinessLocationFormGroup from "../components/business/BusinessLocationFormGroup";

    export default {
        props: {
            /*
            certifications: {
                type: Array,
                default: () => [],
            },
            */
            caregivers: {
                type: Object,
                default: () => {},
            },
        },

        mixins: [FormatsDates],
        components: {BusinessLocationFormGroup, BusinessLocationSelect},

        mounted() {
        },

        data() {
            return {
                form: new Form({
                    caregiver_id: '',
                    days_range: 30,
                    show_expired: false,
                    active: '',
                    name: '',
                    business_id: ''
                }),
                items: {},
                totalRows: 0,
                perPage: 15,
                currentPage: 1,
                sortBy: null,
                sortDesc: false,
                editModalVisible: false,
                filter: null,
                sendingEmail: false,
                loading: false,
                certifications: [],
                certification: [],
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

        computed: {

        },

        methods: {
            details(item, index, button) {
                this.selectedItem = item;
                this.modalDetails.data = JSON.stringify(item, null, 2);
                this.modalDetails.index = index;
                //this.$root.$emit('bv::show::modal','caregiverEditModal', button);
                this.editModalVisible = true;
            },

            resetModal() {
                this.modalDetails.data = '';
                this.modalDetails.index = '';
            },

            onFiltered(filteredItems) {
                // Trigger pagination to update the number of buttons/pages due to filtering
                this.totalRows = filteredItems.length;
                this.currentPage = 1;
            },

            sendEmailReminder(item) {
                if (item.sendingEmail) {
                    return;
                }
                item.sendingEmail = true;
                axios.get('/business/caregivers/licenses/' + item.id + '/send-reminder')
                    .then(response => {
                        console.log(response.data);
                        window.alerts.addMessage('success', 'Reminder email sent.');
                        item.sendingEmail = false;
                    }).catch(error => {
                        console.error(error.response);
                        item.sendingEmail = false;
                    });
            },

            getCountdown(date) {
                if(moment().isSameOrAfter(date)) {
                    return 'Already Expired';
                }

                return moment(date).toNow(true);
            },

            generateReport() {

                this.form.get('/business/reports/certification_expirations_filter/')
                    .then(response => {

                        //this.setCertifications(response.data);

                        this.certifications = response.data1;
                        // /console.log(response.data);
                    })
                    .catch((e) => {
                        console.log(e);
                    })
                    .finally(() => {
                    });
            },

            setCertifications(certs){
                this.certifications = certs;
            },

        },


        watch: {
            'form.show_expired': function(isShowingExpired) {
               if(isShowingExpired) {
                   this.form.days_range = 0;
               }
            }
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
