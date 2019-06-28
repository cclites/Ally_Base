<template>
    <b-card>
        <b-row class="mb-2">
            <b-col md="2">
                <business-location-form-group
                        v-model="form.businesses"
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
                        v-model="form.days"
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
                    {{ getCountdown(row.item.expiration_date) }}
                </template>
                <template slot="actions" scope="row">
                    <b-btn size="sm" :href="'/business/caregivers/' + row.item.caregiver_id">View Caregiver</b-btn>
                    <b-btn size="sm" @click="sendEmailReminder(row.item)" :disabled="row.item.sendingEmail">
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
        },

        mixins: [FormatsDates],
        components: {BusinessLocationFormGroup},

        mounted() {
            this.totalRows = this.items.length;
        },

        data() {
            return {
                form: new Form({
                    caregiver_id: '',
                    days: 30,
                    show_expired: false,
                    active: '',
                    name: '',
                    businesses: '',
                    json: 1,
                }),
                totalRows: 0,
                perPage: 25,
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
                this.form.get('/business/reports/caregiver-expirations')
                    .then(response => {
                        this.items = response.data;
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
