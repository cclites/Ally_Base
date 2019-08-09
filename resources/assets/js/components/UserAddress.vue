<template>
    <b-card :header="title"
        header-bg-variant="info"
        header-text-variant="white"
        >
        <form @submit.prevent="saveAddress()" @keydown="form.clearError($event.target.name)">
            <b-row>

                <b-col lg="12">

                    <b-form-group label="Address Line 1" label-for="address1" label-class="required">

                        <b-form-input v-model="form.address1" type="text" required  />
                        <input-help :form="form" field="address1" text="Enter your street number and name here."></input-help>
                    </b-form-group>
                    <b-form-group label="Address Line 2" label-for="address2">

                        <b-form-input type="text" v-model="form.address2" />
                        <input-help :form="form" field="address2" text="Enter an apartment number or additional address info here. (Optional)"></input-help>
                    </b-form-group>
                    <b-form-group label="City" label-for="city">

                        <b-form-input type="text" v-model="form.city" />
                        <input-help :form="form" field="city" text="Enter the city here."></input-help>
                    </b-form-group>
                    <b-form-group label="State" label-for="state">

                        <b-form-input type="text" v-model="form.state" />
                        <input-help :form="form" field="state" text="Enter the state or province here."></input-help>
                    </b-form-group>
                    <b-form-group label="Zip Code" label-for="zip">

                        <b-form-input type="text" v-model="form.zip" />
                        <input-help :form="form" field="zip" text="Enter the zip code or postal code here."></input-help>
                    </b-form-group>
                    <b-form-group label="County" label-for="county">

                        <b-form-input type="text" v-model="form.county" />
                        <input-help :form="form" field="county" text="Enter the county here. (Optional)"></input-help>
                    </b-form-group>
                    <b-form-group label="Country" label-for="country">

                        <b-form-select :options="countries.getOptions()" v-model="form.country" />
                        <input-help :form="form" field="country" text="Select the country from the drop down."></input-help>
                    </b-form-group>

                    <b-form-group label="Notes" label-for="notes" v-if=" hasNotes ">

                        <b-form-textarea v-model=" form.notes " :state=" form.notes ? ( form.notes.length <= 255 ? null : false ) : null " size="md" rows="3" max-rows="3" placeholder="Add any address-relevant notes. Like gate codes, park in the back, etc."></b-form-textarea>
                        <input-help :form="form" field="notes" text="maximum 255 characters"></input-help>
                    </b-form-group>
                    <div v-else style="height:157px" class="d-md-none d-lg-block"></div>

                </b-col>
            </b-row>
            <b-row>
                <b-col lg="12">
                    <b-button variant="success" type="submit" :disabled="authInactive">Save Address</b-button>
                </b-col>
            </b-row>
        </form>
    </b-card>
</template>

<script>

    // Erik 8/6/19 =>
    // as for the addition of the v-if, v-else div with nothing but spacing on the notes ( bottom of form )..
    // I added this so that the two forms could be the same size next to eachother ( UI consideration ).
    // Alternative could be having the notes field there but disabled.. or maybe add notes for billing address?
    // Open to ideas..

    // also adapted the header so that it didnt change size when the subheading for the client-address moved to the next line.
    // feel free to remove these comments

    import Countries from '../classes/Countries';
    import AuthUser from '../mixins/AuthUser';

    export default {

        mixins: [ AuthUser ],

        props: {

            'title'    : '',
            'type'     : '',
            'user'     : null,
            'address'  : null,
            'action'   : null,
            'hasNotes' : false
        },

        data() {
            return {
                form: new Form(),
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

                    address1 : this.address.address1,
                    address2 : this.address.address2,
                    city     : this.address.city,
                    state    : this.address.state,
                    zip      : this.address.zip,
                    country  : ( this.address.country ) ? this.address.country : 'US',
                    county   : this.address.county,
                    notes    : this.address.notes || ''
                });
            }
        }
    }
</script>
