<template>
    <div>
        <b-card header="Select a Office Location" header-variant="info">
            <b-row>
                <b-col lg="4" md="6">
                    <business-location-select v-model="businessId"></business-location-select>
                </b-col>
            </b-row>
        </b-card>
        <div class="row" v-if="business.id">
            <div class="col-lg-6">
                <b-card header="Deposit Bank Account">
                    <bank-account-form :businessId="business.id" :account="business.bank_account || {}" :submit-url="'/business/settings/bank-account/deposit'" />
                </b-card>
            </div>
            <div class="col-lg-6">
                <b-card header="Payment Bank Account">
                    <bank-account-form :businessId="business.id" :account="business.payment_account || {}" :submit-url="'/business/settings/bank-account/payment'" />
                </b-card>
            </div>
        </div>
    </div>
</template>

<script>
    import BusinessLocationSelect from "../BusinessLocationSelect";

    export default {
        name: "BusinessBankAccounts",
        components: {BusinessLocationSelect},
        props: {
            business: {
                type: Object,
                default: () => {},
            }
        },

        data() {
            return {
                businessId: this.business.id,
            }
        },

        methods: {

        },

        watch: {
            businessId(newVal, oldVal) {
                if (newVal && newVal != oldVal) {
                    window.location.href = '/business/settings/bank-accounts/' + newVal;
                }
            }
        }
    }
</script>

<style scoped>

</style>