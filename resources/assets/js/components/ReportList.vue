<template>
    <b-row>
        <b-col md="6" v-for="(categoryColumn, index) in categoryColumns" :key="index">
            <b-card v-for="category in categoryColumns[index]" :key="category.id">
                <div slot="header">
                    <template v-for="icon in category.icons">
                        <i :class="icon" class="mr-2"></i>
                    </template>
                    {{ category.name }}
                </div>
                <div class="table-responsive mt-3">
                    <b-table bordered striped hover show-empty
                             :items="items[category.id]"
                             :fields="fields"
                             @filtered="onFiltered"
                    >
                        <template slot="name" scope="row">
                            <a :href="row.item.url">{{ row.item.name }}</a>
                        </template>
                    </b-table>
                </div>
            </b-card>
        </b-col>
    </b-row>
</template>

<script>
    export default {
        props: {
            data: {
                type: '',
            }
        },

        data() {
            return {
                role: window.AuthUser,
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
                categories: [
                    {
                        id: 1,
                        name: 'Prospects and Sales',
                        icons: ['fa fa-dollar'],
                        col: 1
                    },
                    {
                        id: 2,
                        name: 'Clients',
                        icons: ['fas fa-user'],
                        col: 1
                    },
                    {
                        id: 3,
                        name: 'Caregivers',
                        icons: ['fas fa-user-md'],
                        col: 1
                    },
                    {
                        id: 4,
                        name: 'Custom report builder - Coming soon!',
                        icons: ['fa fa-gears'],
                        col: 2
                    },
                    {
                        id: 5,
                        name: 'Billing and Payments',
                        icons: ['fa fa-credit-card', 'fa fa-dollar'],
                        col: 2
                    },
                    {
                        id: 6,
                        name: 'Schedules',
                        icons: ['fa fa-calendar'],
                        col: 2
                    },
                    {
                        id: 7,
                        name: 'Other Reports',
                        icons: ['fas fa-paper-plane'],
                        col: 2
                    }
                ]
            }
        },

        mounted() {
            this.totalRows = this.items.length;
        },

        computed: {
            categoryColumns() {
                return _.groupBy(this.categories, 'col')
            },

            items() {
                if (this.role.role_type === 'admin') {
                    let data = [
                        {
                            name: 'Unsettled Report',
                            url: 'reports/unsettled',
                            description: '',
                            category: 5
                        },
                        {
                            name: 'Reconciliation Report',
                            url: 'reports/reconciliation',
                            description: 'See detailed breakdown of each transaction with your bank',
                            category: 5
                        },
                        {
                            name: 'Failed Transactions',
                            url: 'failed_transactions',
                            description: '',
                            category: 5
                        },
                        {
                            name: 'Pending Transactions',
                            url: 'reports/pending_transactions',
                            description: '',
                            category: 5
                        },
                        {
                            name: 'On Hold Report',
                            url: 'reports/on_hold',
                            description: '',
                            category: 5
                        },
                        {
                            name: 'Failed Deposits',
                            url: 'deposits/failed',
                            description: '',
                            category: 5
                        },
                        {
                            name: 'Shared Shifts',
                            url: 'reports/shared_shifts',
                            description: '',
                            category: 7
                        },
                        {
                            name: 'Unpaid Shifts',
                            url: 'reports/unpaid_shifts',
                            description: '',
                            category: 5
                        },
                        {
                            name: 'Missing Deposit Accounts',
                            url: 'reports/caregivers/deposits-missing-bank-account',
                            description: '',
                            category: 3
                        },
                        {
                            name: 'Financial Summary',
                            url: 'reports/finances',
                            description: '',
                            category: 5
                        },
                        {
                            name: 'Client Caregiver Visits',
                            url: 'reports/client-caregiver-visits',
                            description: '',
                            category: 7
                        },
                        {
                            name: 'Active Clients Report',
                            url: 'reports/active-clients',
                            description: '',
                            category: 2
                        },
                        {
                            name: 'Bank Report',
                            url: 'reports/bucket',
                            description: '',
                            category: 5
                        },
                        {
                            name: 'EVV Report',
                            url: 'reports/evv',
                            description: '',
                            category: 7
                        },
                        {
                            name: 'Emails Report',
                            url: 'reports/emails',
                            description: '',
                            category: 7
                        },
                        {
                            name: 'Audit Log',
                            url: 'audit-log',
                            description: '',
                            category: 7
                        },
                    ];
                    return _.groupBy(data, 'category');
                } else if (this.role.role_type === 'office_user') {
                    let data = [
                        {
                            name: 'EVV Report',
                            url: 'reports/evv',
                            description: 'Details on each attempted clock in and clock out',
                            category: 7
                        },
                        {
                            name: 'Reconciliation Report',
                            url: 'reports/reconciliation',
                            description: 'See detailed breakdown of each transaction with your bank',
                            category: 5
                        },
                        // { name: 'Billing Forcast', url: 'reports/billing-forcast', description: 'See forecasting billing amounts based on scheduled and completed visits' },
                        {
                            name: 'Client Directory',
                            url: 'clients',
                            description: 'Shows the full list of clients',
                            category: 2
                        },
                        {
                            name: 'Caregiver Directory',
                            url: 'caregivers',
                            description: 'Shows the full list of caregivers',
                            category: 3
                        },
                        {
                            name: 'Credit Card Expiration',
                            url: 'reports/credit-card-expiration',
                            description: 'See clients with expiring credit cards',
                            category: 2
                        },
                        {
                            name: 'Prospects',
                            url: 'reports/prospects',
                            description: 'Shows the list of prospective clients',
                            category: 1
                        },
                        {
                            name: 'Caregiver Applications',
                            url: 'caregivers/applications',
                            description: 'See all caregiver applicants',
                            category: 3
                        },
                        {
                            name: 'Caregiver Cert & License Expirations',
                            url: 'reports/certification_expirations',
                            description: 'See a list of caregivers with an expiring certification or license',
                            category: 3
                        },
                        {
                            name: 'Claims Report',
                            url: 'reports/claims-report',
                            description: 'Generate a claim file that can be sent to insurance carriers or Medicaid & VA payers',
                            category: 5
                        },
                        {
                            name: 'Export Timesheets',
                            url: 'reports/export-timesheets',
                            description: 'Export timesheets for offline storage',
                            category: 5
                        },
                        {
                            name: 'Client Contacts',
                            url: 'reports/contacts?type=client',
                            description: 'See a list of clients and their phone numbers and email address',
                            category: 2
                        },
                        {
                            name: 'Caregiver Contacts',
                            url: 'reports/contacts?type=caregiver',
                            description: 'See a list of caregivers and their phone numbers and email address',
                            category: 3
                        },
                        {
                            name: 'Client & Caregiver Rates',
                            url: 'reports/client_caregivers',
                            description: 'View all rates between each client and caregiver',
                            category: 2
                        },
                        {
                            name: 'Referral Sources',
                            url: 'reports/referral-sources',
                            description: 'List of referral sources and how many clients have been referred by each',
                            category: 1
                        },
                        {
                            name: 'Shifts by Caregiver',
                            url: 'reports/caregiver-shifts',
                            description: 'See how many shifts have been worked by a caregiver',
                            category: 3
                        },
                        {
                            name: 'Shifts by Client',
                            url: 'reports/client-shifts',
                            description: 'See how many shifts a client has received',
                            category: 2
                        },
                        {
                            name: 'Caregiver Overtime',
                            url: 'reports/overtime',
                            description: 'See what caregivers are at risk of overtime',
                            category: 3
                        },
                        // { name: 'Accounts Receivable', url: 'reports/', description: 'Shows each client with an outstanding balance' },
                        // { name: 'Generate Invoice', url: 'reports/', description: 'This will create an invoice in PDF that can be send to a client with an outstanding balance' },
                        {
                            name: 'Printable Schedules',
                            url: 'reports/printable-schedule',
                            description: 'Print schedules to PDF to be used for on call or offline purposes',
                            category: 6
                        },
                        // { name: 'Client Progression Report', url: 'reports/', description: 'See how a client is progressing over time' },
                        {
                            name: 'Clients Missing Payment Methods',
                            url: 'reports/clients-missing-payment-methods',
                            description: 'Shows all clients missing a payment method',
                            category: 2
                        },
                        {
                            name: 'Caregivers Missing Bank Accounts',
                            url: 'reports/caregivers-missing-bank-accounts',
                            description: 'Shows all caregivers missing bank accounts',
                            category: 3
                        },
                        {
                            name: 'Client & Caregiver Onboard Status',
                            url: 'reports/onboard-status',
                            description: 'See the onboard status for clients and caregivers and send electronic signup link',
                            category: 7
                        },
                        {
                            name: 'Payment History',
                            url: 'reports/payments',
                            description: 'See client charges and caregiver payments over time',
                            category: 5
                        },

                        // { name: 'Clients Without Email', url: 'reports/client-email-missing', description: '' },
                        // { name: 'Client Online Setup', url: 'reports/clients-onboarded', description: '' },
                        // { name: 'Caregiver Online Setup', url: 'reports/caregivers-onboarded', description: '' },
                    ];

                    if (this.data.type === 'Agency') {
                        data.push({
                            name: 'ADP and Paychex',
                            url: 'javascript:;',
                            description: '',
                            category: 7
                        });
                    }

                    return _.groupBy(data, 'category');
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
