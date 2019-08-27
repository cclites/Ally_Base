<template>

    <div>

        <b-row>

            <b-col sm="6" class="mb-2"><strong>Client:</strong> {{ claim.client.name }}</b-col>
            <b-col sm="6" class="mb-2"><strong>Payer Code:</strong> {{ claim.payer_code }}</b-col>
            <b-col sm="6" class="mb-2"><strong>Payer Name:</strong> {{ claim.payer_name }}</b-col>
        </b-row>
        <b-row>

            <b-col sm="6" class="mb-2"><strong>Medicaid ID:</strong> {{ claim.client_medicaid_id }}</b-col>
            <b-col sm="6" class="mb-2"><strong>Medicaid Diagnosis Codes:</strong> {{ claim.client_medicaid_diagnosis_codes }}</b-col>
        </b-row>
        <b-row>

            <b-col sm="6" class="mb-2"><strong>Client Plan Code:</strong> {{ claim.plan_code }}</b-col>
            <b-col sm="6" class="mb-2"><strong>Transmission Method:</strong> {{ claim.transmission_method }}</b-col>
        </b-row>

        <b-row class="mb-2">

            <b-col sm="12">

                <div class="mb-2">

                    <strong>Claim Services:</strong>
                </div>
                <div class="table-responsive">

                    <table class="table table-bordered table-fit-more table-striped table-hover mb-0">

                        <thead>

                            <tr>
                                <th>Caregiver</th>
                                <th>Caregiver Gender</th>
                                <th>Caregiver D.O.B</th>
                                <th>Caregiver SSN</th>
                                <th>Caregiver Medicaid ID</th>
                                <th>Caregiver Comments</th>
                                <th>Service Name</th>
                                <th>Service Code</th>
                                <th>Service Charge</th>
                                <th>Service Balance</th>
                                <th>Service Duration</th>
                                <th>Client Rate</th>
                                <th>Client Address 1</th>
                                <th>Client Address 2</th>
                                <th>Client City</th>
                                <th>Client State</th>
                                <th>Client Zip</th>
                                <th>Client Latitude</th>
                                <th>Client Longitude</th>
                                <th>Checked In #</th>
                                <th>Checked Out #</th>
                                <th>Checked In Latitude</th>
                                <th>Checked Out Longitude</th>
                                <th>Scheduled Start</th>
                                <th>Scheduled End</th>
                                <th>Visit Start</th>
                                <th>Visit End</th>
                                <th>Activities</th>
                                <th>EVV Start</th>
                                <th>EVV End</th>
                                <th>EVV Method In</th>
                                <th>EVV Method Out</th>
                            </tr>
                        </thead>
                        <tbody>

                            <tr v-for=" ( service, i ) in claimableServices " :key=" i ">

                                <td>{{ service.claimable.caregiver_first_name + ' ' + service.claimable.caregiver_last_name }}</td>
                                <td>{{ service.claimable.gender }}</td>
                                <td>{{ service.claimable.caregiver_dob }}</td>
                                <td>{{ service.claimable.caregiver_ssn }}</td>
                                <td>{{ service.claimable.caregiver_medicaid_id }}</td>
                                <td>{{ service.claimable.caregiver_comments }}</td>
                                <td>{{ service.claimable.service_name }}</td>
                                <td>{{ service.claimable.service_code }}</td>
                                <td>{{ service.amount }}</td>
                                <td>{{ service.balance }}</td>
                                <td>{{ service.units }}</td>
                                <td>{{ service.rate }}</td>
                                <td>{{ service.claimable.address1 }}</td>
                                <td>{{ service.claimable.address2 }}</td>
                                <td>{{ service.claimable.city }}</td>
                                <td>{{ service.claimable.state }}</td>
                                <td>{{ service.claimable.zip }}</td>
                                <td>{{ service.claimable.latitude }}</td>
                                <td>{{ service.claimable.longitude }}</td>
                                <td>{{ service.claimable.checked_in_number }}</td>
                                <td>{{ service.claimable.checked_out_number }}</td>
                                <td>{{ service.claimable.checked_in_latitude }}</td>
                                <td>{{ service.claimable.checked_out_longitude }}</td>
                                <td>{{ service.claimable.scheduled_start_time }}</td>
                                <td>{{ service.claimable.scheduled_end_time }}</td>
                                <td>{{ service.claimable.visit_start_time }}</td>
                                <td>{{ service.claimable.visit_end_time }}</td>
                                <td>{{ service.claimable.activities }}</td>
                                <td>{{ service.claimable.evv_start_time }}</td>
                                <td>{{ service.claimable.evv_end_time }}</td>
                                <td>{{ service.claimable.evv_method_in }}</td>
                                <td>{{ service.claimable.evv_method_out }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </b-col>
        </b-row>

        <b-row class="mb-2">

            <b-col sm="12">

                <div class="mb-2">

                    <strong>Claim Expenses:</strong>
                </div>

                <div class="table-responsive">

                    <table class="table table-sm mb-0">

                        <thead>

                            <tr>
                                <th>Expense Name</th>
                                <th>Expense Charge</th>
                                <th>Expense Balance</th>
                                <th>Expense Units</th>
                                <th>Expense Rate</th>
                                <th>Expense Date</th>
                                <th>Expense Notes</th>
                            </tr>
                        </thead>
                        <tbody>

                            <tr v-for=" ( expense, j ) in claimableExpenses " :key=" j ">

                                <td>{{ expense.claimable.name }}</td>
                                <td>{{ expense.amount }}</td>
                                <td>{{ expense.balance }}</td>
                                <td>{{ expense.units }}</td>
                                <td>{{ expense.rate }}</td>
                                <td>{{ expense.claimable.date }}</td>
                                <td>{{ expense.claimable.notes }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </b-col>
        </b-row>
    </div>
</template>

<script>

    // import authUser from '../../mixins/AuthUser';
    // import ShiftServices from "../../mixins/ShiftServices";
    // import FormatsNumbers from "../../mixins/FormatsNumbers";

    export default {

        // mixins: [ authUser, ShiftServices, FormatsNumbers ],

        props: {

            claim: {

                type    : Object,
                default : () => { return {} },
            },
        },

        data: () => ({

        }),

        computed: {

            claimableServices(){

                if( !this.claim.id ) return [];

                return this.claim.items.filter( item => item.claimable_type == 'App\\ClaimableService' );
            },
            claimableExpenses(){

                if( !this.claim.id ) return [];

                return this.claim.items.filter( item => item.claimable_type == 'App\\ClaimableExpense' );
            }
        },

        methods: {

        },

        created() {

        },
    }
</script>

<style scoped>

    ul {

        padding-inline-start: 1.5rem;
        margin-bottom: 0px;
    }
</style>