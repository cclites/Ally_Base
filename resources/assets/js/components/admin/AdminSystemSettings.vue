<template>
    <b-card
            header="Ally Contact Settings"
            header-text-variant="white"
            header-bg-variant="info"
    >
        <b-row>
            <b-col>
                <b-form-group label="Name">
                    <b-input v-model="form.company_name"></b-input>
                </b-form-group>
            </b-col>
        </b-row>
        <b-row>
            <b-col>
                <b-form-group label="Address">
                    <b-input v-model="form.company_address1"></b-input>
                </b-form-group>
            </b-col>
        </b-row>
        <b-row>
            <b-col>
                <b-form-group label="Address 2">
                    <b-input v-model="form.company_address2"></b-input>
                </b-form-group>
            </b-col>
        </b-row>
        <b-row>
            <b-col>
                <b-form-group label="City">
                    <b-input v-model="form.company_city"></b-input>
                </b-form-group>
            </b-col>
            <b-col>
                <b-form-group label="State">
                    <b-input v-model="form.company_state"></b-input>
                </b-form-group>
            </b-col>
            <b-col>
                <b-form-group label="Zip">
                    <b-input v-model="form.company_zip"></b-input>
                </b-form-group>
            </b-col>
        </b-row>
        <b-row>
            <b-col>
                <b-form-group label="Contact Name">
                    <b-input v-model="form.company_contact_name"></b-input>
                </b-form-group>
            </b-col>
            <b-col>
                <b-form-group label="Contact Phone">
                    <b-input v-model="form.company_contact_phone"></b-input>
                </b-form-group>
            </b-col>
        </b-row>
        <b-row>
            <b-col>
                <b-form-group label="Contact Email">
                    <b-input v-model="form.company_contact_email"></b-input>
                </b-form-group>
            </b-col>
            <b-col>
                <b-form-group label="EIN">
                    <b-input v-model="form.company_ein"></b-input>
                </b-form-group>
            </b-col>
        </b-row>
        <b-row>
            <b-col>
                <b-form-group label="&nbsp;" class="float-right">
                    <b-btn variant="info" @click="save()">Save</b-btn>
                </b-form-group>
             </b-col>
        </b-row>
    </b-card>
</template>

<script>
    export default {
        name: "AdminSystemSettings",
        props: {
            settings: {
                type: Object,
                default: []
            }
        },
        data() {
            return {
                form: new Form(this.settings),
            }
        },
        methods: {
            save(){
                this.form.patch("admin-contact-info")
                    .then(response => {
                        echo(response);
                    })
                    .catch( e => {})
                    .finally(() => {});
            }
        }
    }
</script>

<style scoped>

</style>