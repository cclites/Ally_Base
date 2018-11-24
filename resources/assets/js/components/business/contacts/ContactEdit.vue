<template>
    <b-card :header="title"
        header-bg-variant="info"
        header-text-variant="white"
        >
        <form @submit.prevent="saveProfile()" @keydown="form.clearError($event.target.name)">
            <b-row>
                <b-col lg="6">
                    <b-form-group label="First Name" label-for="firstname">
                        <b-form-input
                                id="firstname"
                                name="firstname"
                                type="text"
                                v-model="form.firstname"
                                required
                        >
                        </b-form-input>
                        <input-help :form="form" field="firstname" text="Enter their first name."></input-help>
                    </b-form-group>
                    <b-form-group label="Last Name" label-for="lastname">
                        <b-form-input
                                id="lastname"
                                name="lastname"
                                type="text"
                                v-model="form.lastname"
                                required
                        >
                        </b-form-input>
                        <input-help :form="form" field="lastname" text="Enter their last name."></input-help>
                    </b-form-group>
                    <b-form-group label="Job Title" label-for="title">
                        <b-form-input
                            id="title"
                            name="title"
                            type="text"
                            v-model="form.title"
                        ></b-form-input>
                        <input-help :form="form" field="title" text="Enter their job title."></input-help>
                    </b-form-group>
                    <b-form-group label="Company" label-for="company">
                        <b-form-input
                            id="company"
                            name="company"
                            type="text"
                            v-model="form.company"
                        ></b-form-input>
                        <input-help :form="form" field="company" text="Enter their company."></input-help>
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Email Address" label-for="email">
                        <b-form-input
                                id="email"
                                name="email"
                                type="email"
                                v-model="form.email"
                        >
                        </b-form-input>
                        <input-help :form="form" field="email"
                                    text="Enter their email address. Ex: user@domain.com"></input-help>
                    </b-form-group>
                    <b-form-group label="Phone Number" label-for="phone">
                        <mask-input v-model="form.phone" name="phone"></mask-input>
                        <input-help :form="form" field="phone" text="Enter full phone number."></input-help>
                    </b-form-group>
                    <b-form-group label="General Notes" label-for="general_notes">
                        <b-form-textarea v-model="form.general_notes" id="general_notes" :rows="3"></b-form-textarea>
                        <input-help :form="form" field="general_notes" text="You can use this input to enter some notes about this contact."></input-help>
                    </b-form-group>
                </b-col>
            </b-row>
            <hr />
            <b-row>
                <b-col lg="6">
                    <b-form-group label="Address Line 1" label-for="address1">
                        <b-form-input
                                id="address1"
                                name="address1"
                                type="text"
                                v-model="form.address1"
                        >
                        </b-form-input>
                        <input-help :form="form" field="address1" text="Enter your street number and name here."></input-help>
                    </b-form-group>
                    <b-form-group label="Address Line 2" label-for="address2">
                        <b-form-input
                                id="address2"
                                name="address2"
                                type="text"
                                v-model="form.address2"
                        >
                        </b-form-input>
                        <input-help :form="form" field="address2" text="Enter an apartment number or additional address info here. (Optional)"></input-help>
                    </b-form-group>
                    <b-form-group label="City" label-for="city">
                        <b-form-input
                                id="city"
                                name="city"
                                type="text"
                                v-model="form.city"
                        >
                        </b-form-input>
                        <input-help :form="form" field="city" text="Enter the city here."></input-help>
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="State" label-for="state">
                        <b-form-select name="state" id="state" :options="states.getOptions()" v-model="form.state">
                        </b-form-select>
                        <input-help :form="form" field="state" text="Select the state from the drop down."></input-help>
                    </b-form-group>
                    <b-form-group label="Zip Code" label-for="zip">
                        <b-form-input
                                id="zip"
                                name="zip"
                                type="text"
                                v-model="form.zip"
                        >
                        </b-form-input>
                        <input-help :form="form" field="zip" text="Enter the zip code or postal code here."></input-help>
                    </b-form-group>
                    <b-form-group label="Country" label-for="country">
                        <b-form-select name="country" id="country" :options="countries.getOptions()" v-model="form.country">
                        </b-form-select>
                        <input-help :form="form" field="country" text="Select the country from the drop down."></input-help>
                    </b-form-group>
                </b-col>
            </b-row>
            <hr />
            <b-row>
                <b-col lg="12">
                    <submit-button variant="success"
                                   type="submit"
                                   :submitting="submitting"
                    >
                        Save
                    </submit-button>
                    <b-btn @click="destroy(contact)" v-if="contact" variant="danger">Delete Contact</b-btn>
                </b-col>
            </b-row>
        </form>
    </b-card>
</template>

<script>
    import Countries from "../../../classes/Countries";
    import States from "../../../classes/States";

    export default {
        props: ['contact'],

        data() {
            return {
                form: new Form({
                    'firstname': this.getOriginal('firstname'),
                    'lastname': this.getOriginal('lastname'),
                    'email': this.getOriginal('email'),
                    'client_type': this.getOriginal('client_type'),
                    'business_id': this.getOriginal('business_id'),
                    'title': this.getOriginal('title'),
                    'company': this.getOriginal('company'),
                    'phone': this.getOriginal('phone'),
                    'address1': this.getOriginal('address1'),
                    'address2': this.getOriginal('address2'),
                    'city': this.getOriginal('city'),
                    'state': this.getOriginal('state'),
                    'zip': this.getOriginal('zip'),
                    'country': this.getOriginal('country', 'US'),
                    'general_notes': this.getOriginal('general_notes'),
                }),
                submitting: false,
                countries: new Countries(),
                states: new States(),
            }
        },

        computed: {
            title() {
                return this.contact ? 'Edit Contact' : 'New Contact';
            }
        },

        mounted() {
        },

        methods: {

            getOriginal(field, defaultValue = "") {
                return this.contact ? this.contact[field] : defaultValue;
            },

            async saveProfile() {
                this.submitting = true;
                try {
                    let response;
                    if (this.contact) {
                        response = await this.form.patch(`/business/contacts/${this.contact.id}`);
                    }
                    else {
                        response = await this.form.post('/business/contacts');
                    }
                }
                catch(error) {}
                this.submitting = false;
            },

            destroy(item) {
                if (!confirm(`Are you sure you wish to delete ${item.firstname} ${item.lastname}?`)) return;
                let form = new Form({});
                form.submit('delete', `/business/contacts/${item.id}`);
            },

            closemodal(status) {
                this.show = status;
            }
        },



    }
</script>

<style scoped>
    .pad-top {
        padding-top: 16px;
    }
</style>