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
                <template slot="countdown" scope="row">
                    {{ getCountdown(row.item.expiration_date) }}
                </template>
                <template slot="actions" scope="row">
                    <b-btn size="sm" :href="'/business/caregivers/' + row.item.caregiver_id">View Caregiver</b-btn>
                    <b-btn size="sm" @click="sendEmailReminder(row.item)">
                        <i class="fa fa-spinner fa-spin" v-if="row.item.sendingEmail"></i>
                        <i class="fa fa-envelope" v-else></i>
                        Email Reminder
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
    </b-card>
</template>

<script>
    import FormatsDates from '../mixins/FormatsDates';
    import BusinessLocationSelect from "../components/business/BusinessLocationSelect";
    import BusinessLocationFormGroup from "../components/business/BusinessLocationFormGroup";

    export default {
        props: {
            certifications: {
                type: Array,
                default: () => [],
            },
        },

        mixins: [FormatsDates],
        components: {BusinessLocationFormGroup, BusinessLocationSelect},

        mounted() {
            //this.totalRows = this.items.length;
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
                totalRows: 0,
                perPage: 15,
                currentPage: 1,
                sortBy: null,
                sortDesc: false,
                editModalVisible: false,
                filter: null,
                sendingEmail: false,
                loading: false,
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
            items() {



            },

            caregivers() {
                let caregivers = _.map(this.certifications, (cert) => {
                    return {
                        'id': cert.caregiver_id,
                        'name': cert.caregiver_name
                    }
                });

                return _.uniqBy(caregivers, 'id');
            }
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

                this.form.get('/certification_expirations_filter/')
                    .then(({data}) => {
                        console.log(response.data);
                    })
                    .catch((e) => {
                        console.log(e);
                    })
                    .finally(() => {
                    });
            },
                /*
                form.get('/certification_expirations')
                    .then(response => {
                        console.log(response.data);
                    }).catch(error => {
                    console.error(error.response);
                });
*/

/*
                axios.get(url, {params: {params}} )
                    .then(response => {
                        console.log(response.data);
                        //window.alerts.addMessage('success', 'Reminder email sent.');
                        //item.sendingEmail = false;
                    }).catch(error => {
                    console.error(error.response);
                    //item.sendingEmail = false;
                });*/
                /*
                const {business_id, caregiver_id, show_expired, days_range, active, name} = this.form;

                let certifications = this.certifications.map(cert => {
                    cert.sendingEmail = false;
                    return cert;
                });

                if(caregiver_id) {
                    certifications = certifications.filter(cert => cert.caregiver_id == caregiver_id);
                }

                if(name) {
                    certifications = certifications.filter(cert => cert.name.match(new RegExp(name, 'i')));
                }

                if(show_expired) {
                    certifications = certifications.filter(cert => moment(cert.expiration_date).isSameOrBefore(moment()));
                }

                if(days_range >= 0 && !show_expired) {
                    certifications = certifications.filter(cert => {
                        const expirateAt = moment(cert.expiration_date, 'YYYY-MM-DD');
                        return expirateAt.isBetween(moment(), moment().add(days_range, 'days'));
                    });
                }

                if(active !== '') {
                    certifications = certifications.filter(cert => cert.caregiver_active == active);
                }

                return certifications;
                */


        }

        ,
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
