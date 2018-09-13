<template>
    <div>
        <section class="c-card mt-3">
            <div class="c-card-title">General Preference</div>
            <div class="c-card-body">
                <div class="mt-1">When we create a new Customer, Employee, or Vendor in your QuickBook file, how would you like us to format the name?</div>
                <b-row>
                    <b-col md="3" sm="4">
                        <b-form-select v-model="name" id="name" class="mb-3 mt-1">
                            <option value="">Please Select</option>
                            <option value="Robert Smith">Robert Smith</option>
                        </b-form-select>
                    </b-col>
                </b-row>

                <div>Which QuickBooks Account do you use for your Accounts Receivables?</div>
                <b-row>
                    <b-col md="3" sm="4">
                        <b-form-select v-model="account" id="account">
                            <option value="">Please Select</option>
                        </b-form-select>
                    </b-col>
                </b-row>
            </div>
        </section>

        <section class="c-card mt-5">
            <div class="c-card-title">Billing</div>
            <div class="c-card-body">
                <div class="mt-1">What item name do you use for your caregiving services?</div>
                <b-row>
                    <b-col md="3" sm="4">
                        <b-form-select v-model="caregivingServices" id="caregiving_services" class="mb-3 mt-1">
                            <option value="">Please Select</option>
                            <option value="Referral Fees">Referral Fees</option>
                        </b-form-select>
                    </b-col>
                </b-row>
                <div class="mt-1">What item name do you use for mileage charged to clients on invoice?</div>
                <b-row>
                    <b-col md="3" sm="4">
                        <b-form-select v-model="mileage" id="mileage" class="mb-3 mt-1">
                            <option value="">Please Select</option>
                            <option value="Reimbursable Mileage">Reimbursable Mileage</option>
                        </b-form-select>
                    </b-col>
                </b-row>
                <div class="mt-1">What item name do you use for expenses charged to clients on invoice?</div>
                <b-row>
                    <b-col md="3" sm="4">
                        <b-form-select v-model="expenses" id="expenses" class="mb-3 mt-1">
                            <option value="">Please Select</option>
                            <option value="Reimbursable Expenses">Reimbursable Expenses</option>
                        </b-form-select>
                    </b-col>
                </b-row>
                <div class="mt-1">What item name do you use for credits applied to clients on invoice?</div>
                <b-row>
                    <b-col md="3" sm="4">
                        <b-form-select v-model="credits" id="credits" class="mb-3 mt-1">
                            <option value="">Please Select</option>
                            <option value="Credits">Credits</option>
                        </b-form-select>
                    </b-col>
                </b-row>
            </div>
        </section>

        <section class="c-card mt-5">
            <div class="c-card-title">Payroll</div>
            <div class="c-card-body">
                <div class="mt-1">What item name do you use for time worked by Independent Contractors?</div>
                <b-row>
                    <b-col md="3" sm="4">
                        <b-form-select v-model="independentContractors" id="independent_contractors" class="mb-3 mt-1">
                            <option value="">Please Select</option>
                            <option value="Care Services">Care Services</option>
                        </b-form-select>
                    </b-col>
                </b-row>
                <div class="mt-1">What item name do you use for mileage reimbursement to caregivers?</div>
                <b-row>
                    <b-col md="3" sm="4">
                        <b-form-select v-model="mileageReimbursement" id="mileage_reimbursement" class="mb-3 mt-1">
                            <option value="">Please Select</option>
                            <option value="Mileage">Mileage</option>
                        </b-form-select>
                    </b-col>
                </b-row>
                <div class="mt-1">What item name do you use for expense reimbursement to caregivers?</div>
                <b-row>
                    <b-col md="3" sm="4">
                        <b-form-select v-model="expenseReimbursement" id="expense_reimbursement" class="mb-3 mt-1">
                            <option value="">Please Select</option>
                            <option value="Caregiver Expenses">Caregiver Expenses</option>
                        </b-form-select>
                    </b-col>
                </b-row>
                <div class="mt-1">What item name do you use for credit applied to caregivers?</div>
                <b-row>
                    <b-col md="3" sm="4">
                        <b-form-select v-model="caregiversCredit" id="caregivers_credit" class="mb-3 mt-1">
                            <option value="">Please Select</option>
                            <option value="Caregiver Credit">Caregiver Credit</option>
                        </b-form-select>
                    </b-col>
                </b-row>
            </div>
        </section>

        <b-row>
            <b-col md="12" class="text-right mt-4">
                <b-button :variant="'success'">Save</b-button>
            </b-col>
        </b-row>
    </div>
</template>

<script>

    export default {

        data() {
            return {
                name: 'Robert Smith',
                account: '',
                caregivingServices: 'Referral Fees',
                mileage: 'Reimbursable Mileage',
                expenses: 'Reimbursable Expenses',
                credits: 'Credits',
                independentContractors: 'Care Services',
                mileageReimbursement: 'Mileage',
                expenseReimbursement: 'Caregiver Expenses',
                caregiversCredit: 'Caregiver Credit'
            }
        },

        computed: {

        },

        methods: {

        },

        mounted() {

        },

    }
</script>

<style lang="scss">
    .c-card {
        border: 1px solid #d7dfe3;
        border-radius: 4px;
        -webkit-box-shadow: 0px 5px 20px rgba(0, 0, 0, 0.05);
        box-shadow: 0px 5px 20px rgba(0, 0, 0, 0.05);
        position: relative;

        .c-card-title {
            position: absolute;
            font-size: 25px;
            top: -19px;
            left: 30px;
            background-color: #ffffff;
            padding: 0 5px;
        }

        .c-card-body {
            padding: 1.25rem;
        }
    }
</style>
