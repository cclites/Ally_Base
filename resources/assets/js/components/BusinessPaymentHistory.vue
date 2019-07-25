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
                <!--<template slot="actions" scope="row">-->

                <!--</template>-->
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
                        key: 'client_name',
                        label: 'Client',
                        sortable: true,
                    },
                    {
                        key: 'amount',
                        label: 'Client Charge',
                        sortable: true,
                    },
                    {
                        key: 'business_allotment',
                        label: 'Provider Allotment',
                        sortable: true,
                    },
//                    'actions'
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
                        id: payment.id,
                        date: moment(payment.date).format('L'),
                        client_name: payment.client_name,
                        amount: '$' + payment.amount,
                        business_allotment: '$' + payment.business_allotment,
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
        },
    }
</script>
