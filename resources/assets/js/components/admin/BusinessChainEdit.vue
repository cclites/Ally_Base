<template>
    <b-card header="Edit Business Chain"
            header-bg-variant="info"
            header-text-variant="white"
    >
        <form @submit.prevent="submitForm()" @keydown="form.clearError($event.target.name)">
            <b-row>
                <b-col lg="6">
                    <b-form-group label="Provider Name" label-for="name" label-class="required">
                        <b-form-input
                                id="name"
                                name="name"
                                type="text"
                                v-model="form.name"
                                required
                        >
                        </b-form-input>
                        <input-help :form="form" field="name" text="Enter the provider name."></input-help>
                    </b-form-group>
                    <b-form-group label="Phone Number" label-for="phone1" label-class="required">
                        <mask-input type="phone"
                                id="phone1"
                                name="phone1"
                                v-model="form.phone1"
                                required
                        >
                        </mask-input>
                        <input-help :form="form" field="phone1" text="Enter their phone number."></input-help>
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Street Address" label-for="address1">
                        <b-form-input
                                id="address1"
                                name="address1"
                                type="text"
                                v-model="form.address1"
                        >
                        </b-form-input>
                        <input-help :form="form" field="address1" text="Enter their street address."></input-help>
                    </b-form-group>
                    <b-form-group label="City" label-for="city">
                        <b-form-input
                                id="city"
                                name="city"
                                type="text"
                                v-model="form.city"
                        >
                        </b-form-input>
                        <input-help :form="form" field="city" text="Enter their city."></input-help>
                    </b-form-group>
                    <b-form-group label="State" label-for="state">
                        <b-form-input
                                id="state"
                                name="state"
                                type="text"
                                maxlength="2"
                                v-model="form.state"
                        >
                        </b-form-input>
                        <input-help :form="form" field="state" text="Enter their state abbreviation."></input-help>
                    </b-form-group>
                    <b-form-group label="Zip Code" label-for="zip">
                        <b-form-input
                                id="zip"
                                name="zip"
                                type="text"
                                v-model="form.zip"
                        >
                        </b-form-input>
                        <input-help :form="form" field="zip" text="Enter their zip code."></input-help>
                    </b-form-group>
                    <b-form-group label="Calendar Start of Week" label-for="calendar_week_start" label-class="required">
                        <b-form-select id="calendar_week_start" v-model="form.calendar_week_start">

                            <option v-for=" ( value, day ) in CALENDAR_START_OF_WEEK " :key=" value " :value=" value ">{{ day | lowercase }}</option>
                        </b-form-select>
                        <input-help :form="form"
                                    field="calendar_week_start"
                                    text="Choose which day of the week should show first on the Schedules page." />
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="12">
                    <b-button id="save-chain" variant="success" type="submit">Save Chain</b-button>
                </b-col>
            </b-row>
        </form>
    </b-card>
</template>

<script>

    import Constants from '../../mixins/Constants';

    export default {

        mixins : [ Constants ],
        props: {
            chain: {},
        },

        data() {
            return {
                form: new Form({
                    name: this.chain.name,
                    phone1: this.chain.phone1,
                    address1: this.chain.address1,
                    city: this.chain.city,
                    state: this.chain.state,
                    zip: this.chain.zip,
                    calendar_week_start: this.chain.calendar_week_start,
                })
            }
        },

        mounted() {
        },

        methods: {
            submitForm() {
                this.form.patch('/admin/chains/' + this.chain.id);
            }
        }
    }
</script>
