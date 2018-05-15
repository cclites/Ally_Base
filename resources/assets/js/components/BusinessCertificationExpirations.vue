<template>
    <b-card>
        <b-row class="mb-2">
            <b-col lg="3">
                <b-form-group label="Caregiver">
                    <b-form-select v-model="caregiver_id">
                        <option value="">All</option>
                        <option v-for="caregiver in caregivers" :value="caregiver.id" :key="caregiver.id">{{ caregiver.name }}</option>
                    </b-form-select>
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
    export default {
        props: {
            'certifications': {
                default() {
                    return [];
                }
            },
        },

        mixins: [FormatsDates],

        mounted() {
            this.totalRows = this.items.length;
        },

        data() {
            return {
                caregiver_id: '',
                totalRows: 0,
                perPage: 15,
                currentPage: 1,
                sortBy: null,
                sortDesc: false,
                editModalVisible: false,
                filter: null,
                sendingEmail: false,
                fields: [
                    {
                        key: 'name',
                        label: 'Name',
                        sortable: true,
                    },
                    {
                        key: 'caregiver_name',
                        label: 'Caregiver',
                        sortable: true,
                    },
                    {
                        key: 'expiration_date',
                        label: 'Expiration Date',
                        sortable: true,
                        formatter: (value) => { return this.formatDate(value) }
                    },
                    {
                        key: 'caregiver_name',
                        label: 'Caregiver',
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
                let certifications = _.map(this.certifications, (cert) => {
                    cert.sendingEmail = false;
                    return cert;
                });

                if (this.caregiver_id !== '') {
                    return _.filter(certifications, (cert) => {
                        return cert.caregiver_id === this.caregiver_id;
                    });
                }
                return certifications;
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
//                this.$root.$emit('bv::show::modal','caregiverEditModal', button);
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
            }
        }
    }
</script>
