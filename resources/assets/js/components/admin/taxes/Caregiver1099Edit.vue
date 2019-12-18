<template>
    <b-card
            header="Edit Caregiver 1099"
            header-text-variant="white"
            header-bg-variant="info"
    >
        <!--
          CLIENT INPUTS
         -->
        <b-row>
            <b-col>
                <b-form-group
                        label="Client First Name"
                        label-for="client_first_name"
                >
                    <b-form-input
                            id="client_first_name"
                            v-model="caregiver1099.client_first_name"
                            required
                            placeholder="First Name"
                    ></b-form-input>
                </b-form-group>
            </b-col>
            <b-col>
                <b-form-group
                        label="Client Last Name"
                        label-for="client_last_name"
                >
                    <b-form-input
                            id="client_last_name"
                            v-model="caregiver1099.client_last_name"
                            required
                            placeholder="Last Name"
                    ></b-form-input>
                </b-form-group>
            </b-col>
            <b-col>
                <b-form-group
                        label="SSN"
                        label-for="client_ssn"
                >
                    <b-form-input
                            id="client_ssn"
                            v-model="caregiver1099.client_ssn"
                            required
                            placeholder="SSN"
                    ></b-form-input>
                </b-form-group>
            </b-col>
        </b-row>


        <b-form-group
                label="Client Address"
                label-for="client_address1"
        >
            <b-form-input
                    id="client_address1"
                    v-model="caregiver1099.client_address1"
                    required
                    placeholder="Address"
            ></b-form-input>
        </b-form-group>
        <b-form-group
                label="Client Address 2"
                label-for="client_address2"
        >
            <b-form-input
                    id="client_address2"
                    v-model="caregiver1099.client_address2"
                    placeholder="Address2"
            ></b-form-input>
        </b-form-group>

        <b-row>
            <b-col>
                <b-form-group
                        label="Client City"
                        label-for="client_city"
                >
                    <b-form-input
                            id="client_city"
                            v-model="caregiver1099.client_city"
                            required
                            placeholder="Client City"
                    ></b-form-input>
                </b-form-group>
            </b-col>
            <b-col>
                <b-form-group
                        label="Client state"
                        label-for="client_state"
                >
                    <b-form-input
                            id="client_state"
                            v-model="caregiver1099.client_state"
                            required
                            placeholder="Client State"
                    ></b-form-input>
                </b-form-group>
            </b-col>
            <b-col>
                <b-form-group
                        label="Client Zip"
                        label-for="client_zip"
                >
                    <b-form-input
                            id="client_zip"
                            v-model="caregiver1099.client_zip"
                            required
                            placeholder="Client Zip"
                    ></b-form-input>
                </b-form-group>
            </b-col>
        </b-row>

        <hr>

        <!--
          CAREGIVER INPUTS
         -->
        <b-row>
            <b-col>
                <b-form-group
                        label="Caregiver First Name"
                        label-for="caregiver_first_name"
                >
                    <b-form-input
                            id="caregiver_first_name"
                            v-model="caregiver1099.caregiver_first_name"
                            required
                            placeholder="First Name"
                    ></b-form-input>
                </b-form-group>
            </b-col>

            <b-col>
                <b-form-group
                        label="Caregiver Last Name"
                        label-for="caregiver_last_name"
                >
                    <b-form-input
                            id="caregiver_last_name"
                            v-model="caregiver1099.caregiver_last_name"
                            required
                            placeholder="Last Name"
                    ></b-form-input>
                </b-form-group>
            </b-col>

            <b-col>
                <b-form-group
                        label="Caregiver SSN"
                        label-for="caregiver_ssn"
                >
                    <b-form-input
                            id="caregiver_ssn"
                            v-model="caregiver1099.caregiver_ssn"
                            required
                            placeholder="SSN"
                    ></b-form-input>
                </b-form-group>
            </b-col>
        </b-row>


        <b-form-group
                label="Caregiver Address"
                label-for="caregiver_address1"
        >
            <b-form-input
                    id="caregiver_address1"
                    v-model="caregiver1099.caregiver_address1"
                    required
                    placeholder="Address"
            ></b-form-input>
        </b-form-group>
        <b-form-group
                label="Caregiver Address 2"
                label-for="caregiver_address2"
        >
            <b-form-input
                    id="caregiver_address2"
                    v-model="caregiver1099.caregiver_address2"
                    placeholder="Address2"
            ></b-form-input>
        </b-form-group>

        <b-row>
            <b-col>
                <b-form-group
                        label="Caregiver City"
                        label-for="caregiver_city"
                >
                    <b-form-input
                            id="caregiver_city"
                            v-model="caregiver1099.caregiver_city"
                            required
                            placeholder="Caregiver City"
                    ></b-form-input>
                </b-form-group>
            </b-col>
            <b-col>
                <b-form-group
                        label="Caregiver State"
                        label-for="caregiver_state"
                >
                    <b-form-input
                            id="caregiver_state"
                            v-model="caregiver1099.caregiver_state"
                            required
                            placeholder="Caregiver State"
                    ></b-form-input>
                </b-form-group>
            </b-col>
            <b-col>
                <b-form-group
                        label="Caregiver Zip"
                        label-for="caregiver_zip"
                >
                    <b-form-input
                            id="caregiver_zip"
                            v-model="caregiver1099.caregiver_zip"
                            required
                            placeholder="Caregiver Zip"
                    ></b-form-input>
                </b-form-group>
            </b-col>
        </b-row>

        <br>
        <hr>

        <b-row>
            <b-col>
                <b-form-group
                        label="1099 Payment Total"
                        label-for="payment_total"
                >
                    <b-form-input
                            id="payment_total"
                            v-model="caregiver1099.payment_total"
                            required
                            placeholder="1099 Payment Total"
                    ></b-form-input>
                </b-form-group>
            </b-col>

            <b-col>
                <b-form-group
                        label="Year"
                        label-for="year"
                >
                    <b-form-input
                            id="year"
                            v-model="caregiver1099.year"
                            required
                            placeholder="Year"
                    ></b-form-input>
                </b-form-group>
            </b-col>

        </b-row>
    </b-card>
</template>

<script>
    export default {
        name: "Caregiver1099Edit",
        props: {
            caregiver1099: {},
        },
        data() {
            return {
            };
        },
        methods: {
        },
    }
</script>

<style scoped>

</style>