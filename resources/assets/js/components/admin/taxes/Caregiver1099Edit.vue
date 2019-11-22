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
                        label-for="client_fname"
                >
                    <b-form-input
                            id="client_fname"
                            v-model="caregiver1099.client_fname"
                            required
                            placeholder="First Name"
                    ></b-form-input>
                </b-form-group>
            </b-col>
            <b-col>
                <b-form-group
                        label="Client Last Name"
                        label-for="client_lname"
                >
                    <b-form-input
                            id="client_lname"
                            v-model="caregiver1099.client_lname"
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
                    required
                    placeholder="Address2"
            ></b-form-input>
        </b-form-group>
        <b-form-group
                label="Client City, State, Zip"
                label-for="client_address3"
        >
            <b-form-input
                    id="client_address3"
                    v-model="caregiver1099.client_address3"
                    required
                    placeholder="Address2"
            ></b-form-input>
        </b-form-group>

        <!--
          CAREGIVER INPUTS
         -->
        <b-row>
            <b-col>
                <b-form-group
                        label="Caregiver First Name"
                        label-for="caregiver_fname"
                >
                    <b-form-input
                            id="caregiver_fname"
                            v-model="caregiver1099.caregiver_fname"
                            required
                            placeholder="First Name"
                    ></b-form-input>
                </b-form-group>
            </b-col>

            <b-col>
                <b-form-group
                        label="Caregiver Last Name"
                        label-for="caregiver_lname"
                >
                    <b-form-input
                            id="caregiver_lname"
                            v-model="caregiver1099.caregiver_lname"
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
                    required
                    placeholder="Address2"
            ></b-form-input>
        </b-form-group>
        <b-form-group
                label="Caregiver City, State, Zip"
                label-for="caregiver_address3"
        >
            <b-form-input
                    id="caregiver_address3"
                    v-model="caregiver1099.caregiver_address3"
                    required
                    placeholder="Address2"
            ></b-form-input>
        </b-form-group>

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