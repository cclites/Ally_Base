<template>
    <b-card :header="title"
        header-bg-variant="info"
        header-text-variant="white"
        >
        <form @submit.prevent="saveAddress()" @keydown="form.clearError($event.target.name)">
            <b-row>
                <b-col lg="12">
                    <b-form-group label="Address Line 1" label-for="address1">
                        <b-form-input
                            id="address1"
                            name="address1"
                            type="text"
                            required
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
                        <input-help :form="form" field="address2" text="Enter an apartment number or additional address info here."></input-help>
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
                    <b-form-group label="State" label-for="state">
                        <b-form-input
                            id="state"
                            name="state"
                            type="text"
                            v-model="form.state"
                            >
                        </b-form-input>
                        <input-help :form="form" field="state" text="Enter the state or province here."></input-help>
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
            <b-row>
                <b-col lg="12">
                    <b-button id="save-profile" variant="success" type="submit">Save Address</b-button>
                </b-col>
            </b-row>
        </form>
    </b-card>
</template>

<script>
    export default {
        props: {
            'title': '',
            'type': '',
            'user': null,
            'address': null,
            'action': null,
        },

        data() {
            return {
                form: {},
                countries: new Countries()
            }
        },

        mounted() {
            this.setForm();
            this.$watch('address', () => {this.setForm()});
        },

        methods: {

            saveAddress() {
                let action = (this.action) ? this.action : '/profile/address/' + this.type;
                this.form.post(action)
                    .then(() => {
                        window.scroll(0, 0);
                    });
            },

            setForm() {
                this.form = new Form({
                    address1: this.address.address1,
                    address2: this.address.address2,
                    city: this.address.city,
                    state: this.address.state,
                    zip: this.address.zip,
                    country: (this.address.country) ? this.address.country : 'US',
                });
            }
        }
    }
</script>
