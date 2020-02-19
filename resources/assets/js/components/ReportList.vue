<template>
<div>
    <b-row>
        <b-col md="6" v-for="category in categories" :key="category.id">
            <b-card>
                <div slot="header">
                    <template v-for="icon in category.icons">
                        <i :class="icon" :key="icon" class="mr-2"></i>
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
                        <template slot="description" scope="row">
                            {{ row.item.description }}
                            <div v-if="row.item.hidden === true" class="text-danger">This is only shown for admins impersonating office users.</div>
                        </template>
                    </b-table>
                </div>
            </b-card>
        </b-col>
    </b-row>
</div>
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
                        id: 4,
                        name: 'Custom report builder - Coming soon!',
                        icons: ['fa fa-gears'],
                        col: 2
                    },
                    {
                        id: 2,
                        name: 'Clients',
                        icons: ['fa fa-user'],
                        col: 1
                    },
                    {
                        id: 5,
                        name: 'Billing and Payments',
                        icons: ['fa fa-credit-card', 'fa fa-dollar'],
                        col: 2
                    },
                    {
                        id: 3,
                        name: 'Caregivers',
                        icons: ['fa fa-user-md'],
                        col: 1
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
                        icons: ['fa fa-paper-plane'],
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
                const reports = [
                    // For admin only
                    {
                        name: 'Unsettled Report',
                        url: 'reports/unsettled',
                        description: '',
                        category: 5,
                        allowed: ['admin'],
                    },
                    {
                        name: 'Failed Transactions',
                        url: 'failed_transactions',
                        description: '',
                        category: 5,
                        allowed: ['admin'],
                    },
                    {
                        name: 'Pending Transactions',
                        url: 'reports/pending_transactions',
                        description: '',
                        category: 5,
                        allowed: ['admin'],
                    },
                    {
                        name: 'On Hold Report',
                        url: 'reports/on_hold',
                        description: '',
                        category: 5,
                        allowed: ['admin'],
                    },
                    {
                        name: 'Failed Deposits',
                        url: 'deposits/failed',
                        description: '',
                        category: 5,
                        allowed: ['admin'],
                    },
                    {
                        name: 'Shared Shifts',
                        url: 'reports/shared_shifts',
                        description: '',
                        category: 7,
                        allowed: ['admin'],
                    },
                    {
                        name: 'Bad SSN Report (Clients)',
                        url: 'reports/bad-ssn-report/clients',
                        description: '',
                        category: 7,
                        allowed: ['admin'],
                    },
                    {
                        name: 'Bad SSN Report (Caregivers)',
                        url: 'reports/bad-ssn-report/caregivers',
                        description: '',
                        category: 7,
                        allowed: ['admin'],
                    },
                    {
                        name: 'Unpaid Shifts',
                        url: 'reports/unpaid_shifts',
                        description: '',
                        category: 5,
                        allowed: ['admin'],
                    },
                    {
                        name: 'Missing Deposit Accounts',
                        url: 'reports/caregivers/deposits-missing-bank-account',
                        description: '',
                        category: 3,
                        allowed: ['admin'],
                    },
                    {
                        name: 'Financial Summary',
                        url: 'reports/finances',
                        description: '',
                        category: 5,
                        allowed: ['admin'],
                    },
                    {
                        name: 'Client Caregiver Visits',
                        url: 'reports/client-caregiver-visits',
                        description: '',
                        category: 7,
                        allowed: ['admin'],
                    },
                    {
                        name: 'OccAcc Deductible Report',
                        url: '/business/occ-acc-deductibles',
                        description: 'Allows you to select any 1-week timeframe and calculate OccAcc deductions for each eligible caregiver',
                        category: 3,
                        allowed: ['office_user'],
                    },
                    {
                        name: 'Active Clients Report',
                        url: 'reports/active-clients',
                        description: '',
                        category: 2,
                        allowed: ['admin'],
                    },
                    {
                        name: 'Bank Report',
                        url: 'reports/bucket',
                        description: '',
                        category: 5,
                        allowed: ['admin'],
                    },
                    {
                        name: 'Paid Billed Audit Report',
                        url: '/admin/reports/paid-billed-audit-report',
                        description: '',
                        category: 5,
                        allowed: ['admin'],
                    },
                    {
                        name: 'Emails Report',
                        url: 'reports/emails',
                        description: '',
                        category: 7,
                        allowed: ['admin'],
                    },
                    {
                        name: 'Audit Log',
                        url: 'audit-log',
                        description: '',
                        category: 7,
                        allowed: ['admin'],
                    },
                    {
                        name: 'Total Charges Report',
                        url: 'reports/total_charges_report',
                        description: '',
                        category: 5,
                        allowed: ['admin'],
                    },
                    {
                        name: 'Total Deposits Report',
                        url: 'reports/total_deposits_report',
                        description: '',
                        category: 5,
                        allowed: ['admin'],
                    },
                    {
                        name: 'Charges vs Deposits Report',
                        url: 'reports/charges-vs-deposits',
                        description: 'This report shows all incoming charges and outgoing deposits for each chain for a given period.',
                        category: 5,
                        allowed: ['admin'],
                    },

                    // Shared between admin and office users
                    {
                        name: 'Reconciliation Report',
                        url: 'reports/reconciliation',
                        description: 'See detailed breakdown of each transaction with your bank',
                        category: 5,
                        allowed: ['admin','office_user'],
                    },
                    {
                        name: 'EVV Report',
                        url: 'reports/evv',
                        description: 'Details on each attempted clock in and clock out',
                        category: 7,
                        allowed: ['admin','office_user'],
                    },

                    // For office users only

                    {
                        name: 'Client Birthdays',
                        url: 'reports/birthdays?type=clients',
                        description: 'Shows the list of clients\'s birthdays',
                        category: 2,
                        allowed: ['office_user'],
                    },
                    {
                        name: 'Client Directory',
                        url: 'reports/client-directory',
                        description: 'Shows the full list of clients',
                        category: 2,
                        allowed: ['office_user'],
                    },
                    {
                        name: 'Avery 5160 Mailing Labels',
                        url: 'reports/avery-labels',
                        description: 'A dedicated reports page for downloading an Avery 5160 PDF labels printout',
                        category: 2,
                        allowed: ['office_user'],
                    },
                    {
                        name: 'Avery 5160 Mailing Labels',
                        url: 'reports/avery-labels',
                        description: 'A dedicated reports page for downloading an Avery 5160 PDF labels printout',
                        category: 3,
                        allowed: ['office_user'],
                    },
                    {
                        name: 'Caregiver Birthdays',
                        url: 'reports/birthdays?type=caregivers',
                        description: 'Shows the list of caregivers\'s birthdays',
                        category: 3,
                        allowed: ['office_user'],
                    },
                    {
                        name: 'Caregiver Anniversary',
                        url: 'reports/anniversary',
                        description: 'Shows the caregivers\'s and their work anniversaries',
                        category: 3,
                        allowed: ['office_user'],
                    },
                    {
                        name: 'Caregiver Directory',
                        url: 'reports/caregiver-directory',
                        description: 'Shows the full list of caregivers',
                        category: 3,
                        allowed: ['office_user'],
                    },
                    {
                        name: 'Credit Card Expiration',
                        url: 'reports/credit-card-expiration',
                        description: 'See clients with expiring credit cards',
                        category: 2,
                        allowed: ['office_user'],
                    },
                    {
                        name: 'Prospects',
                        url: 'reports/prospects',
                        description: 'Shows the list of prospective clients',
                        category: 1,
                        allowed: ['office_user'],
                    },
                    {
                        name: 'Caregiver Applications',
                        url: 'caregivers/applications',
                        description: 'See all caregiver applicants',
                        category: 3,
                        allowed: ['office_user'],
                    },
                    {
                        name: 'Caregiver Expirations',
                        url: 'reports/caregiver-expirations',
                        description: 'See a list of caregivers with an expiring certification or license',
                        category: 3,
                        allowed: ['office_user'],
                    },
                    {
                        name: 'Claims Report',
                        url: 'reports/claims-report',
                        description: 'Generate a claim file that can be sent to insurance carriers or Medicaid & VA payers',
                        category: 5,
                        allowed: ['office_user'],
                    },
                    {
                        name: 'Export Timesheets',
                        url: 'reports/export-timesheets',
                        description: 'Export timesheets for offline storage',
                        category: 5,
                        allowed: ['office_user'],
                    },
                    {
                        name: 'Client Contacts',
                        url: 'reports/contacts?type=client',
                        description: 'See a list of clients and their phone numbers and email address',
                        category: 2,
                        allowed: ['office_user'],
                    },
                    {
                        name: 'Caregiver Contacts',
                        url: 'reports/contacts?type=caregiver',
                        description: 'See a list of caregivers and their phone numbers and email address',
                        category: 3,
                        allowed: ['office_user'],
                    },
                    {
                        name: 'Client & Caregiver Rates',
                        url: 'reports/client_caregivers',
                        description: 'View all rates between each client and caregiver',
                        category: 2,
                        allowed: ['office_user'],
                    },
                    {
                        name: 'Client Referral Sources',
                        url: 'reports/client-referral-sources',
                        description: 'List of referral sources and how many clients have been referred by each',
                        category: 1,
                        allowed: ['office_user'],
                    },
                    {
                        name: 'Caregiver Referral Sources',
                        url: 'reports/caregiver-referral-sources',
                        description: 'List of referral sources and how many caregivers have been referred by each',
                        category: 1,
                        allowed: ['office_user'],
                    },
                    {
                        name: 'Revenue',
                        url: 'reports/revenue',
                        description: 'Shows the total run down of the revenue and profit',
                        category: 1,
                        allowed: ['office_user'],
                    },
                    {
                        name: 'Sales Pipeline',
                        url: 'reports/sales-pipeline',
                        description: 'Shows the current status of the sales pipelines',
                        category: 1,
                        allowed: ['office_user'],
                    },
                    {
                        name: 'Shifts by Caregiver',
                        url: 'reports/caregiver-shifts',
                        description: 'See how many shifts have been worked by a caregiver',
                        category: 3,
                        allowed: ['office_user'],
                    },
                    {
                        name: 'Shifts by Client',
                        url: 'reports/client-shifts',
                        description: 'See how many shifts a client has received',
                        category: 2,
                        allowed: ['office_user'],
                    },
                    {
                        name: 'Client Service Coordinators',
                        url: 'reports/services-coordinator',
                        description: 'Shows all clients each service coordinator is assigned',
                        category: 2,
                        allowed: ['office_user'],
                    },
                    {
                        name: 'Caregiver Overtime',
                        url: 'reports/overtime',
                        description: 'See what caregivers are at risk of overtime',
                        category: 3,
                        allowed: ['office_user'],
                    },
                    {
                        name: 'Printable Schedules',
                        url: 'reports/printable-schedule',
                        description: 'Print schedules to PDF to be used for on call or offline purposes',
                        category: 6,
                        allowed: ['office_user'],
                    },
                    {
                        name: 'Available Shifts',
                        url: 'reports/available-shifts',
                        description: 'Available shifts report',
                        category: 6,
                        allowed: ['office_user'],
                    },
                    // { Removed as per ALLY-1394
                    //     name: 'Clients Missing Payment Methods',
                    //     url: 'reports/clients-missing-payment-methods',
                    //     description: 'Shows all clients missing a payment method',
                    //     category: 2,
                    //     allowed: [['office_user'],
                    // },
                    // { Removed as per ALLY-1394
                    //     name: 'Caregivers Missing Bank Accounts',
                    //     url: 'reports/caregivers-missing-bank-accounts',
                    //     description: 'Shows all caregivers missing bank accounts',
                    //     category: 3,
                    //     allowed: [['office_user'],
                    // },
                    {
                        name: 'Client & Caregiver Onboard Status',
                        url: 'reports/onboard-status',
                        description: 'See the onboard status for clients and caregivers and send electronic signup link',
                        category: 7,
                        allowed: ['office_user'],
                    },
                    // { Removed as per ALLY-1459
                    //     name: 'Payment History',
                    //     url: 'reports/payments',
                    //     description: 'See client charges and caregiver payments over time',
                    //     category: 5,
                    //     allowed: ['office_user'],
                    // },
                    {
                        name: 'Client Statistics',
                        url: 'reports/client-stats',
                        description: 'See client stats',
                        category: 2,
                        allowed: ['office_user'],
                    },
                    {
                        name: 'Caregiver Statistics',
                        url: 'reports/caregiver-stats',
                        description: 'See caregiver stats',
                        category: 3,
                        allowed: ['office_user'],
                    },
                    {
                        name: 'Projected Billing',
                        url: 'reports/projected-billing',
                        description: 'See forecasting billing amounts based on scheduled visits',
                        category: 5,
                        allowed: ['office_user'],
                    },
                    {
                        name: 'Payroll Export',
                        url: 'reports/payroll-export',
                        description: 'Export Caregiver earnings to ADP/Paychex/BCN',
                        category: 5,
                        allowed: ['office_user'],
                    },
                    {
                        name: 'Disaster Code Plan Report',
                        url: 'reports/disaster-plan-report',
                        description: 'Export Client Disaster Codes and Plans',
                        category: 2,
                        allowed: ['office_user'],
                    },
                    /*
                    Chad Note: Leaving this for now in case they want it back
                    {
                        name: 'Medicaid Billing',
                        url: 'reports/medicaid-billing',
                        description: 'Medicaid Billing Report',
                        category: 5,
                        allowed: ['office_user'],
                    },*/
                    {
                        name: '3rd Party Payer',
                        url: 'reports/third-party-payer',
                        description: '3rd Party Payer Report',
                        category: 5,
                        allowed: ['office_user'],
                    },
                    {
                        name: 'Offline AR Aging',
                        url: 'reports/offline-ar-aging',
                        description: 'Offline Invoice AR Aging Report',
                        category: 5,
                        allowed: ['office_user'],
                    },
                    {
                        name: 'Salesperson Commission',
                        url: 'reports/sales-people-commission',
                        description: 'Salesperson Commission Report',
                        category: 5,
                        allowed: ['office_user'],
                    },
                    {
                        name: 'Payers Invoices',
                        url: 'reports/payer-invoice-report',
                        description: 'Payers Invoices Report',
                        category: 5,
                        allowed: ['office_user'],
                    },

                    {
                        name: 'Caregiver Account Setup Status',
                        url: 'reports/account-setup',
                        description: 'Shows a list of caregivers with incomplete account data.',
                        category: 3,
                        allowed: ['office_user'],
                    },

                    { // added as per ALLY-1394
                        name        : 'Client Account Setup Status',
                        url         : 'reports/client-account-setup',
                        description : 'Shows a list of clients with incomplete account data.',
                        category    : 2,
                        allowed     : [ 'office_user' ],
                    },
                    {
                        name: 'Service Authorization Ending Report',
                        url: 'reports/service-auth-ending',
                        description: 'Shows all clients service auths and the dates they are ending.',
                        category: 2,
                        allowed: ['office_user'],
                    },
                    {
                        name: 'Service Authorization Usage Report',
                        url: 'reports/service-auth-usage',
                        description: 'Shows a usage report for client\'s service authorizations.',
                        category: 2,
                        allowed: ['office_user'],
                    },
                    {
                        name: 'Payment Summary by Payment Method',
                        url: 'reports/payment-summary-by-payer',
                        description: 'This report shows a summary of all payments made.',
                        category: 5,
                        allowed: ['office_user'],
                    },
                    {
                        name: 'Invoice Summary by Client',
                        url: 'reports/invoice-summary-by-client',
                        description: 'This report shows invoice or claim totals summarized by client for a specified period.',
                        category: 5,
                        allowed: ['office_user'],
                    },
                    {
                        name: 'Invoice Summary by Client Type',
                        url: 'reports/invoice-summary-by-client-type',
                        description: 'This report shows invoice or claim totals summarized by client type in a specified period.',
                        category: 5,
                        allowed: ['office_user'],
                    },

                    {
                        name: 'Client Face Sheet',
                        url: 'reports/face-sheet?role=client',
                        description: 'Generate Client Face Sheet',
                        category: 2,
                        allowed: ['office_user'],
                    },

                    {
                        name: 'Caregiver Face Sheet',
                        url: 'reports/face-sheet?role=caregiver',
                        description: 'Generate Caregiver Face Sheet',
                        category: 3,
                        allowed: ['office_user'],
                    },


                    // {
                    //     name: 'Batch Invoice',
                    //     url: 'reports/batch-invoice',
                    //     description: 'Print Batch invoices to PDF',
                    //     category: 2,
                    //     allowed: ['office_user'],
                    // },
                    // { name: 'Billing Forcast', url: 'reports/billing-forcast', description: 'See forecasting billing amounts based on scheduled and completed visits' },
                    // { name: 'Accounts Receivable', url: 'reports/', description: 'Shows each client with an outstanding balance' },
                    // { name: 'Generate Invoice', url: 'reports/', description: 'This will create an invoice in PDF that can be send to a client with an outstanding balance' },
                    // { name: 'Client Progression Report', url: 'reports/', description: 'See how a client is progressing over time' },
                    // { name: 'Clients Without Email', url: 'reports/client-email-missing', description: '' },
                    // { name: 'Client Online Setup', url: 'reports/clients-onboarded', description: '' },
                    // { name: 'Caregiver Online Setup', url: 'reports/caregivers-onboarded', description: '' },
                ];

                // Add temporary hidden reports for Admins when impersonating
                if (this.isAdmin) {
                    reports.push(
                        {
                            name: 'Payroll Summary',
                            url: 'reports/payroll-summary-report',
                            description: 'Total caregiver payments over a specified date range',
                            category: 5,
                            allowed: ['office_user'],
                            hidden: true,
                        },
                        {
                            name: 'Client Referrals',
                            url: 'reports/client-referrals',
                            description: 'Client Referrals Report',
                            category: 5,
                            allowed: ['office_user'],
                            hidden: true,
                        },
                        {
                            name: 'Invoice Summary By Salesperson',
                            url: 'reports/invoice-summary-by-salesperson',
                            description: 'Total Client Charges By Salesperson',
                            category: 5,
                            allowed: ['office_user'],
                            hidden: true,
                        },
                        {
                            name: 'Invoice Summary By County',
                            url: 'reports/invoice-summary-by-county',
                            description: 'Invoice Summary Report By County',
                            category: 5,
                            allowed: ['office_user'],
                            hidden: true,
                        },
                    )
                }

                const {role_type} = this.role;
                const filteredByRole = reports.filter(({allowed}) => allowed.find(role => role == role_type));

                if (this.role.role_type === 'office_user' && this.data.type === 'Agency') {
                    filteredByRole.push(
                        {
                            name: 'ADP Payroll Export',
                            url: 'javascript:;',
                            description: '',
                            category: 7
                        },
                        {
                            name: 'Paychex Payroll Export',
                            url: 'javascript:;',
                            description: '',
                            category: 7
                        },
                        {
                            name: 'Payroll Report',
                            url: 'reports/payroll',
                            description: '',
                            category: 7
                        },
                    );
                }

                return _.groupBy(filteredByRole, 'category');
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
