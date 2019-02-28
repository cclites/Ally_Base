<template>
    <div>
        <b-alert show><strong>Note:</strong> When you select OVERTIME or HOLIDAY on the Schedule, the rates will update accordingly based on your settings here.</b-alert>
        <h3 class="f-1">Overtime &amp; Holidy Shift Calculations</h3>

        <b-row>
            <b-col lg="6">
                <b-form-group label="OT Rate Multiplier" label-for="ot_multiplier">
                    <b-form-select
                        id="ot_multiplier"
                        name="ot_multiplier"
                        v-model="form.ot_multiplier"
                    >
                        <option value="1.0">1.0</option>
                        <option value="1.5">1.5x</option>
                        <option value="2.0">2.0x</option>
                    </b-form-select>
                    <input-help :form="form" field="ot_multiplier" text=""></input-help>
                </b-form-group>

                <b-form-group label="When marking a scheduled shift OT do the following">
                    <b-form-radio-group id="ot_behavior" v-model="form.ot_behavior">
                        <div><b-form-radio value="caregiver">Multiply CG rate by the multiplier</b-form-radio></div>
                        <div><b-form-radio value="provider">Multiply the Provider rate by the multiplier</b-form-radio></div>
                        <div><b-form-radio value="both">Multiply both the CG rate and Provider rate by the multiplier</b-form-radio></div>
                        <div><b-form-radio value="">Do nothing &amp; adjust rates manually</b-form-radio></div>
                    </b-form-radio-group>
                    <input-help :form="form" field="ot_behavior" text=""></input-help>
                </b-form-group>
            </b-col>
            <b-col lg="6">
                <b-form-group label="HOL Rate Multiplier" label-for="hol_multiplier">
                    <b-form-select
                        id="hol_multiplier"
                        name="hol_multiplier"
                        v-model="form.hol_multiplier"
                    >
                        <option value="1.0">1.0</option>
                        <option value="1.5">1.5x</option>
                        <option value="2.0">2.0x</option>
                    </b-form-select>
                    <input-help :form="form" field="hol_multiplier" text=""></input-help>
                </b-form-group>

                <b-form-group label="When marking a scheduled shift HOL do the following">
                    <b-form-radio-group id="hol_behavior" v-model="form.hol_behavior">
                        <div><b-form-radio value="caregiver">Multiply CG rate by the multiplier</b-form-radio></div>
                        <div><b-form-radio value="provider">Multiply the Provider rate by the multiplier</b-form-radio></div>
                        <div><b-form-radio value="both">Multiply both the CG rate and Provider rate by the multiplier</b-form-radio></div>
                        <div><b-form-radio value="">Do nothing &amp; adjust rates manually</b-form-radio></div>
                    </b-form-radio-group>
                    <input-help :form="form" field="hol_behavior" text=""></input-help>
                </b-form-group>
            </b-col>
        </b-row>
        <b-row>
            <b-col>
                <hr />
                <b-btn @click="save" variant="info" size="lg">
                    Save Settings
                </b-btn>
            </b-col>
        </b-row>
    </div>
</template>

<script>
    export default {
        props: {
            'business': {},
        },

        data() {
            return {
                form: new Form({
                }),
            }
        },

        computed: {
        },
        
        methods: {
            save() {
                this.form.put('/business/settings/overtime')
                    .then( ({ data }) => {
                        this.$store.commit('updateBusiness', data.data);
                    })
                    .catch(e => {
                    })
            },

            makeForm(business) {
                return new Form({
                    business_id: business.id,
                    ot_multiplier: business.ot_multiplier ? String(business.ot_multiplier) : '1.5',
                    ot_behavior: business.ot_behavior ? business.ot_behavior : '',
                    hol_multiplier: business.hol_multiplier ? String(business.hol_multiplier) : '1.5',
                    hol_behavior: business.hol_behavior ? business.hol_behavior : '',
                });
            },
        },

        mounted() {
            this.form = this.makeForm(this.business);
        },

        watch: {
            business(business, oldBusiness) {
                this.form = this.makeForm(business);
                if (!oldBusiness && business) {
                    this.form = this.makeForm(business);
                    return;
                }

                if (business.id !== oldBusiness.id) {
                    this.form = this.makeForm(business);
                }
            },
        },
    }
</script>
