<template>
    <b-card header="Emergency Contacts"
        header-bg-variant="info"
        header-text-variant="white"
        >
        <form @submit.prevent="saveProfile()" @keydown="form.clearError($event.target.name)">

            <b-row>
                <b-col>
                    <p class="h6 text-primary">Power of Attorney</p>
                    <hr>
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="6">
                    <b-form-group label="First Name">
                        <b-form-input id="poa_first_name"
                                      v-model="form.poa_first_name"></b-form-input>
                    </b-form-group>
                    <b-form-group label="Phone">
                        <b-form-input id="poa_phone"
                                      v-model="form.poa_phone"></b-form-input>
                    </b-form-group>
                    <b-form-group label="Email">
                        <b-form-input id="poa_email"
                                      v-model="form.poa_email"
                                      type="email"></b-form-input>
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Last Name">
                        <b-form-input id="poa_last_name"
                                      v-model="form.poa_last_name"></b-form-input>
                    </b-form-group>
                    <b-form-group label="Relationship">
                        <b-form-input id="poa_relationship"
                                      v-model="form.poa_relationship"></b-form-input>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row>
                <b-col>
                    <p class="h6 text-primary">Physician</p>
                    <hr>
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="6">
                    <b-form-group label="First Name">
                        <b-form-input id="dr_first_name"
                                      v-model="form.dr_first_name"></b-form-input>
                    </b-form-group>
                    <b-form-group label="Phone">
                        <b-form-input id="dr_phone"
                                      v-model="form.dr_phone"></b-form-input>
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Last Name">
                        <b-form-input id="dr_last_name"
                                      v-model="form.dr_last_name"></b-form-input>
                    </b-form-group>
                    <b-form-group label="Fax">
                        <b-form-input id="dr_fax"
                                      v-model="form.dr_fax"></b-form-input>
                    </b-form-group>
                </b-col>
            </b-row>

            <b-row>
                <b-col lg="12">
                    <b-button variant="success" type="submit">Save Emergency Contact</b-button>
                </b-col>
            </b-row>
        </form>
    </b-card>
</template>

<script>
    
    export default {
        props: {
            'client': {}
        },

        components: {
        },

        data() {
            return {
                form: new Form({
                    poa_first_name: this.client.poa_first_name,
                    poa_last_name: this.client.poa_last_name,
                    poa_phone: this.client.poa_phone,
                    poa_email: this.client.poa_email,
                    poa_relationship: this.client.poa_relationship,
                    dr_first_name: this.client.dr_first_name,
                    dr_last_name: this.client.dr_last_name,
                    dr_phone: this.client.dr_phone,
                    dr_fax: this.client.dr_fax
                }),
            }
        },

        mounted() {
            
        },

        methods: {
            async saveProfile() {
                let response = await this.form.patch(`/business/clients/${this.client.id}/emergency-contact`)
            },
        },

        computed: {

        }

    }
</script>

<style scoped>
    .pad-top {
        padding-top: 16px;
    }
</style>
