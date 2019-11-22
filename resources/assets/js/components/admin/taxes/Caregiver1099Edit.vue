<template>
    <div>
        <!--
          CLIENT INPUTS
         -->

        <b-form-group
            label="First Name"
            label-for="client_fname"
            >
            <b-form-input
                    id="client_fname"
                    v-model="form.client_fname"
                    type="text"
                    required
                    placeholder="First Name"
            ></b-form-input>
        </b-form-group>
        <b-form-group
                label="Last Name"
                label-for="client_lname"
        >
            <b-form-input
                    id="client_lname"
                    v-model="form.client_lname"
                    type="text"
                    required
                    placeholder="Last Name"
            ></b-form-input>
        </b-form-group>
        <b-form-group
            label="SSN"
            label-for="client_ssn"
        >
            <b-form-input
                    id="client_ssn"
                    v-model="form.client_ssn"
                    type="text"
                    required
                    placeholder="SSN"
            ></b-form-input>
        </b-form-group>
        <b-form-group
                label="Client Address"
                label-for="client_address1"
        >
            <b-form-input
                    id="client_address1"
                    v-model="form.client_address1"
                    type="text"
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
                    v-model="form.client_address2"
                    type="text"
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
                    v-model="form.client_address3"
                    type="text"
                    required
                    placeholder="Address2"
            ></b-form-input>
        </b-form-group>

        <!--
          CAREGIVER INPUTS
         -->

        <b-form-group
                label="First Name"
                label-for="caregiver_fname"
        >
            <b-form-input
                    id="caregiver_fname"
                    v-model="form.caregiver_fname"
                    type="text"
                    required
                    placeholder="First Name"
            ></b-form-input>
        </b-form-group>
        <b-form-group
                label="Last Name"
                label-for="caregiver_lname"
        >
            <b-form-input
                    id="caregiver_lname"
                    v-model="form.caregiver_lname"
                    type="text"
                    required
                    placeholder="Last Name"
            ></b-form-input>
        </b-form-group>
        <b-form-group
                label="SSN"
                label-for="caregiver_ssn"
        >
            <b-form-input
                    id="caregiver_ssn"
                    v-model="form.caregiver_ssn"
                    type="text"
                    required
                    placeholder="SSN"
            ></b-form-input>
        </b-form-group>
        <b-form-group
                label="Client Address"
                label-for="caregiver_address1"
        >
            <b-form-input
                    id="caregiver_address1"
                    v-model="form.caregiver_address1"
                    type="text"
                    required
                    placeholder="Address"
            ></b-form-input>
        </b-form-group>
        <b-form-group
                label="Client Address 2"
                label-for="caregiver_address2"
        >
            <b-form-input
                    id="caregiver_address2"
                    v-model="form.caregiver_address2"
                    type="text"
                    required
                    placeholder="Address2"
            ></b-form-input>
        </b-form-group>
        <b-form-group
                label="Client City, State, Zip"
                label-for="caregiver_address3"
        >
            <b-form-input
                    id="caregiver_address3"
                    v-model="form.caregiver_address3"
                    type="text"
                    required
                    placeholder="Address2"
            ></b-form-input>
        </b-form-group>
        <b-form-group
                label="Payment Total"
                label-for="payment_total"
        >
            <b-form-input
                    id="payment_total"
                    v-model="form.payment_total"
                    type="numeric"
                    required
                    placeholder="Payment Total"
            ></b-form-input>
        </b-form-group>
        <b-form-group
                label="Year"
                label-for="year"
        >
            <b-form-input
                    id="year"
                    v-model="form.year"
                    type="numeric"
                    required
                    placeholder="Year"
            ></b-form-input>
        </b-form-group>

    </div>
</template>

<script>
    export default {
        name: "Caregiver1099Edit",
        props: {
            caregiver1099: {},
        },
        data() {
            return {
                form: new Form(this.caregiver1099),
            };
        }
    }
</script>

<style scoped>

</style>