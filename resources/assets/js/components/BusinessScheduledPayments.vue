<template>
    <b-card>
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
    export default {
        props: {
            'payments': {
                default() {
                    return [];
                }
            },
        },

        data() {
            return {
                totalRows: 0,
                perPage: 15,
                currentPage: 1,
                sortBy: null,
                sortDesc: false,
                editModalVisible: false,
                filter: null,
                modalDetails: { index:'', data:'' },
                selectedItem: {},
                fields: [
                    {
                        key: 'date',
                        label: 'Date',
                        sortable: true,
                    },
                    {
                        key: 'hours',
                        label: 'Hours',
                        sortable: true,
                    },
                    {
                        key: 'client_name',
                        label: 'Client',
                        sortable: true,
                    },
                    {
                        key: 'caregiver_name',
                        label: 'Caregiver',
                        sortable: true,
                    },
                    {
                        key: 'business_allotment',
                        label: 'Provider Fee',
                        sortable: true,
                    },
                    {
                        key: 'total_payment',
                        label: 'Estimated Total',
                        sortable: true,
                    },
                    {
                        key: 'status',
                        label: 'Status',
                        sortable: true,
                    },
                ]
            }
        },

        mounted() {
            this.totalRows = this.items.length;
        },

        computed: {
            items() {
                return this.payments.map(function(payment) {
                    return {
                        id: payment.shift_id,
                        date: moment(payment.shift_time).format('L'),
                        hours: payment.shift_hours,
                        client_name: payment.client.name,
                        caregiver_name: payment.caregiver.name,
                        total_payment: '$' + payment.total_payment,
                        business_allotment: '$' + payment.business_allotment,
                        status: payment.status,
                    }
                })
            },
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
            }
        }
    }
</script>
