<template>
    <b-card>
        <div class="claim-info">
            <h1>Claim #{{ claim.name }}</h1>
            <b-row>
                <b-col lg="6">
                    <b-form-group label="Client" label-for="business_id">
                        <label><a :href="`/business/clients/${claim.client_id}`" target="_blank">{{ claim.client.name }}</a></label>
                    </b-form-group>
<!--                    <b-form-group label="Office Location" label-for="business_id">-->
<!--                        <label>{{ claim.business.name }}</label>-->
<!--                    </b-form-group>-->
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Related Client Invoice" label-for="client_invoice_id">
                        <label><a :href="`/business/clients/${claim.client_invoice_id}`" target="_blank">#{{ claim.client_invoice.name }}</a></label>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="6">
                    <b-form-group label="Payer" label-for="payer_id">
                        {{ claim.payer.name }}
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Status" label-for="status">
                        <label>{{ snakeToTitleCase(claim.status) }}</label>
                    </b-form-group>
                </b-col>
            </b-row>
        </div>
        <div class="edit-claim-form">
            <b-row>
                <b-col lg="6">
                    <b-form-group label="Client First Name" label-for="client_first_name">
                        <b-form-input
                            v-model="form.client_first_name"
                            id="client_first_name"
                            name="client_first_name"
                            type="text"
                        ></b-form-input>
                        <input-help :form="form" field="client_first_name" text=""></input-help>
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Client Last Name" label-for="client_last_name">
                        <b-form-input
                            v-model="form.client_last_name"
                            id="client_last_name"
                            name="client_last_name"
                            type="text"
                        ></b-form-input>
                        <input-help :form="form" field="client_last_name" text=""></input-help>
                    </b-form-group>
                </b-col>
            </b-row>
    <!--                    client_first_name-->
    <!--                    client_last_name-->
    <!--                    payer_code-->
    <!--                    client_medicaid_id-->
    <!--                    client_dob-->
    <!--                    client_medicaid_diagnosis_codes-->
    <!--                    plan_code-->
    <!--                    transmission_method-->
    <!--                    -->
    <!--                    amount-->
    <!--                    amount_due-->
        </div>
    </b-card>
</template>

<script>
    import FormatsDates from "../../../mixins/FormatsDates";
    import FormatsStrings from "../../../mixins/FormatsStrings";

    export default {
        mixins: [FormatsDates, FormatsStrings],

        props: {
            claim: {
                type: Object,
                default: () => {
                    return {}
                },
            },
        },

        data() {
            return {
            };
        },

        computed: {
        },

        methods: {
            updateClaim() {
                this.process_loading = true;
                axios.patch('/business/claims/' + this.claim.id, this.claim_details)
                    .then(res => {

                        console.log('response: ', res);
                        this.transmitUpdate(res.data);
                    })
                    .catch(err => {

                        console.error(err);
                        alert('Error updating claim');
                    })
                    .finally(() => {

                        this.process_loading = false;
                        this.editing_claim = false;
                    });
            },
        },

        watch: {
        },

        created() {
            if (this.claim) {
            }
        },
    }
</script>

<style >
.claim-info .form-group > label {
    font-weight: 800;
}
</style>