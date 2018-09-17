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
                 :filter="filter"
                 :sort-by.sync="sortBy"
                 :sort-desc.sync="sortDesc"
                 @filtered="onFiltered"
            >
                 <template slot="name" scope="row">
                     <a :href="row.item.url">{{ row.item.name }}</a>
                 </template>
            </b-table>
        </div>

        <!-- <b-row>
            <b-col lg="6">
                <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" />
            </b-col>
            <b-col lg="6" class="text-right">
                Showing {{ perPage < totalRows ? perPage : totalRows }} of {{ totalRows }} results
            </b-col>
        </b-row> -->

    </b-card>
</template>

<script>
export default {
    props: {
        role: Object
    },

    data() {
        return {
            totalRows: 0,
            // perPage: 25,
            // currentPage: 1,
            sortBy: 'name',
            sortDesc: false,
            filter: null,
            fields: [
                {
                    key: 'name',
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
            if(this.role['role_type'] == 'admin') {
                return [
                    { name: 'Unsettled Report', url: 'reports/unsettled', description: '' },
                    { name: 'Reconciliation Report', url: 'reports/reconciliation', description: 'See detailed breakdown of each transaction with your bank' },
                    { name: 'Failed Transactions', url: 'failed_transactions', description: '' },
                    { name: 'Pending Transactions', url: 'reports/pending_transactions', description: '' },
                    { name: 'On Hold Report', url: 'reports/on_hold', description: '' },
                    { name: 'Failed Deposits', url: 'deposits/failed', description: '' },
                    { name: 'Shared Shifts', url: 'reports/shared_shifts', description: '' },
                    { name: 'Unpaid Shifts', url: 'reports/unpaid_shifts', description: '' },
                    { name: 'Missing Deposit Accounts', url: 'reports/caregivers/deposits-missing-bank-account', description: '' },
                    { name: 'Financial Summary', url: 'reports/finances', description: '' },
                    { name: 'Client Caregiver Visits', url: 'reports/client-caregiver-visits', description: '' },
                    { name: 'Active Clients Report', url: 'reports/active-clients', description: '' },
                    { name: 'Bank Report', url: 'reports/bucket', description: '' },
                    { name: 'EVV Report', url: 'reports/evv', description: '' },
                    { name: 'Emails Report', url: 'reports/emails', description: '' },
                    { name: 'Audit Log', url: 'audit-log', description: '' },
                ];
            } else if(this.role['role_type'] == 'office_user') {
                return [
                    { name: 'EVV Report', url: 'reports/evv', description: 'Details on each attempted clock in and clock out' },
                    { name: 'Reconciliation Report', url: 'reports/reconciliation', description: 'See detailed breakdown of each transaction with your bank' },
                    // { name: 'Billing Forcast', url: 'reports/billing-forcast', description: 'See forecasting billing amounts based on scheduled and completed visits' },
                    { name: 'Client Directory', url: 'clients', description: 'Shows the full list of clients' },
                    { name: 'Caregiver Directory', url: 'caregivers', description: 'Shows the full list of caregivers' },
                    { name: 'Credit Card Expiration', url: 'reports/credit-card-expiration', description: 'See clients with expiring credit cards' },
                    { name: 'Prospects', url: 'reports/prospects', description: 'Shows the list of prospective clients' },
                    { name: 'Caregiver Applications', url: 'caregivers/applications', description: 'See all caregiver applicants' },
                    { name: 'Caregiver Cert & License Expirations', url: 'reports/certification_expirations', description: 'See a list of caregivers with an expiring certification or license' },
                    { name: 'Claims Report', url: 'reports/claims-report', description: 'Generate a claim file that can be sent to insurance carriers or Medicaid & VA payers' },
                    { name: 'Export Timesheets', url: 'reports/export-timesheets', description: 'Export timesheets for offline storage' },
                    { name: 'Client Contacts', url: 'reports/contacts?type=client', description: 'See a list of clients and their phone numbers and email address' },
                    { name: 'Caregiver Contacts', url: 'reports/contacts?type=caregiver', description: 'See a list of caregivers and their phone numbers and email address' },
                    { name: 'Client & Caregiver Rates', url: 'reports/client_caregivers', description: 'View all rates between each client and caregiver' },
                    { name: 'Referral Sources', url: 'reports/referral-sources', description: 'List of referral sources and how many clients have been referred by each' },
                    { name: 'Shifts by Caregiver', url: 'reports/caregiver-shifts', description: 'See how many shifts have been worked by a caregiver' },
                    { name: 'Shifts by Client', url: 'reports/client-shifts', description: 'See how many shifts a client has received' },
                    { name: 'Caregiver Overtime', url: 'reports/overtime', description: 'See what caregivers are at risk of overtime' },
                    // { name: 'Accounts Receivable', url: 'reports/', description: 'Shows each client with an outstanding balance' },
                    // { name: 'Generate Invoice', url: 'reports/', description: 'This will create an invoice in PDF that can be send to a client with an outstanding balance' },
                    { name: 'Printable Schedules', url: 'reports/printable-schedule', description: 'Print schedules to PDF to be used for on call or offline purposes' },
                    // { name: 'Client Progression Report', url: 'reports/', description: 'See how a client is progressing over time' },
                    // { name: 'Clients Missing Payment Methods', url: 'reports/', description: 'Shows all clients missing a payment method' },
                    { name: 'Caregivers Missing Bank Accounts', url: 'reports/caregivers-missing-bank-accounts', description: 'Shows all caregivers missing bank accounts' },
                    { name: 'Client & Caregiver Onboard Status', url: 'reports/onboard-status', description: 'See the onboard status for clients and caregivers and send electronic signup link' },
                    { name: 'Payment History', url: 'reports/payments', description: 'See client charges and caregiver payments over time' },

                    // { name: 'Clients Without Email', url: 'reports/client-email-missing', description: '' },
                    // { name: 'Client Online Setup', url: 'reports/clients-onboarded', description: '' },
                    // { name: 'Caregiver Online Setup', url: 'reports/caregivers-onboarded', description: '' },
                ];
            } else {
                return [];
            }
        }
    },

    methods: {
        onFiltered(filteredItems) {
            // Trigger pagination to update the number of buttons/pages due to filtering
            this.totalRows = filteredItems.length;
            this.currentPage = 1;
        }
    }
}
</script>