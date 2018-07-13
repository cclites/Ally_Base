<template>
    <div>
        <b-row>
            <b-col>
                <b-card header="Insurance Data"
                        header-text-variant="white"
                        header-bg-variant="info">
                    <b-row>
                        <b-col lg="6">
                            <b-form-group label="Company Name">
                                <b-form-input v-model="form.ltci_name"></b-form-input>
                            </b-form-group>
                        </b-col>
                        <b-col lg="6">
                            <b-form-group label="Company Address">
                                <b-form-input v-model="form.ltci_address"></b-form-input>
                            </b-form-group>
                        </b-col>
                    </b-row>
                    <b-row>
                        <b-col lg="4">
                            <b-form-group label="Company City">
                                <b-form-input v-model="form.ltci_city"></b-form-input>
                            </b-form-group>
                        </b-col>
                        <b-col lg="4">
                            <b-form-group label="Company State">
                                <b-form-input v-model="form.ltci_state"></b-form-input>
                            </b-form-group>
                        </b-col>
                        <b-col lg="4">
                            <b-form-group label="Company Zip">
                                <b-form-input v-model="form.ltci_zip"></b-form-input>
                            </b-form-group>
                        </b-col>
                    </b-row>
                    <hr>
                    <b-row>
                        <b-col lg="6">
                            <b-form-group label="Policy #">
                                <b-form-input v-model="form.ltci_policy"></b-form-input>
                            </b-form-group>
                        </b-col>
                        <b-col lg="6">
                            <b-form-group label="Claim #">
                                <b-form-input v-model="form.ltci_claim"></b-form-input>
                            </b-form-group>
                        </b-col>
                    </b-row>
                    <b-row>
                        <b-col>
                            <b-btn @click="updateInsuranceInfo()">Update</b-btn>
                        </b-col>
                    </b-row>
                </b-card>
            </b-col>
        </b-row>
    </div>
</template>

<script>
    export default {
        props: ['client'],

        data() {
            return {
                form: new Form({
                    ltci_name: this.client.ltci_name,
                    ltci_address: this.client.ltci_address,
                    ltci_city: this.client.ltci_city,
                    ltci_state: this.client.ltci_state,
                    ltci_zip: this.client.ltci_zip,
                    ltci_policy: this.client.ltci_policy,
                    ltci_claim: this.client.ltci_claim
                })
            }
        },

        methods: {
            updateInsuranceInfo() {
                this.form.put('/business/clients/'+this.client.id+'/ltci');
            }
        }
    }
</script>