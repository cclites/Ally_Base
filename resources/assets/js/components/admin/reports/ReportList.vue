<template>
    <b-card>
        <b-row align-h="end">
            <b-col md="4" class="text-right">
                <b-form-input v-model="filter" placeholder="Type to Search" />
            </b-col>
        </b-row>

        <div class="table-responsive mt-3">
            <b-table bordered striped hover show-empty
                 :items="items"
                 :fields="fields"
                 :current-page="currentPage"
                 :per-page="perPage"
                 :filter="filter"
                 :sort-by.sync="sortBy"
                 :sort-desc.sync="sortDesc"
                 @filtered="onFiltered">

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

        },

        data() {
            return {
                totalRows: 0,
                perPage: 15,
                currentPage: 1,
                sortBy: null,
                sortDesc: false,
                filter: null,
                fields: [
                    {
                        key: 'report_name',
                        label: 'Report Name',
                        sortable: true,
                    },
                    {
                        key: 'description',
                        label: 'What this report shows',
                        sortable: true,
                    },
                ],
            }
        },

        mounted() {
            this.totalRows = this.items.length;
        },

        computed: {
            items() {
                return [
                    { report_name: '<a href="reports/unsettled">Unsettled Report</a>', description: '' },
                    { report_name: '<a href="reports/reconciliation">Reconciliation Report</a>', description: 'See detailed breakdown of each transaction with your bank' },
                    { report_name: '<a href="failed_transactions">Failed Transactions</a>', description: '' },
                    { report_name: '<a href="reports/pending_transactions">Pending Transactions</a>', description: '' },
                    { report_name: '<a href="reports/on_hold">On Hold Report</a>', description: '' },
                    { report_name: '<a href="deposits/failed">Failed Deposits</a>', description: '' },
                    { report_name: '<a href="reports/shared_shifts">Shared Shifts</a>', description: '' },
                    { report_name: '<a href="reports/unpaid_shifts">Unpaid Shifts</a>', description: '' },
                    { report_name: '<a href="reports/caregivers/deposits-missing-bank-account">Missing Deposit Accounts</a>', description: '' },
                    { report_name: '<a href="reports/finances">Financial Summary</a>', description: '' },
                    { report_name: '<a href="reports/client-caregiver-visits">Client Caregiver Visits</a>', description: '' },
                    { report_name: '<a href="reports/active-clients">Active Clients Report</a>', description: '' },
                    { report_name: '<a href="reports/bucket">Bank Report</a>', description: '' },
                    { report_name: '<a href="reports/evv">EVV Report</a>', description: '' },
                    { report_name: '<a href="reports/emails">Emails Report</a>', description: '' },
                    { report_name: '<a href="audit-log">Audit Log</a>', description: '' },
                ]
            }
        },

        methods: {
            editActivity(item) {
                this.selectedItem = item;
                this.activityModal = true;
                this.form = new Form({
                    code: this.selectedItem.code,
                    name: this.selectedItem.name,
                });
            },
            createActivity() {
                this.selectedItem = {};
                this.activityModal = true;
                this.form = new Form({
                    code: null,
                    name: null,
                });
            },
            onFiltered(filteredItems) {
                // Trigger pagination to update the number of buttons/pages due to filtering
                this.totalRows = filteredItems.length;
                this.currentPage = 1;
            }
        }
    }
</script>