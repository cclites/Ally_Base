<template>
    <div>
        <b-row>
            <b-col lg="6">
                <b-form-group label="Payment Type" label-for="payment_type" label-class="required">
                    <b-select name="payment_type" id="payment_type" v-model="form.payment_type" :options="claimRemitTypeOptions" :disabled="form.busy">
                        <template slot="first">
                            <option value="">-- Select a Payment Type --</option>
                        </template>
                    </b-select>
                    <input-help :form="form" field="payment_type" text=""></input-help>
                </b-form-group>

                <b-form-group label="Payment Date" label-for="date" label-class="required">
                    <date-picker v-model="form.date" id="date" :disabled="form.busy" />
                    <input-help :form="form" field="date" text="" />
                </b-form-group>

                <b-form-group label="Reference #" label-for="reference">
                    <b-form-input
                        v-model="form.reference"
                        id="reference"
                        name="reference"
                        type="text"
                        :disabled="form.busy"
                    />
                    <input-help :form="form" field="reference" text=""></input-help>
                </b-form-group>

                <b-form-group label="Amount" label-for="amount" label-class="required">
                    <b-form-input
                        id="amount"
                        name="amount"
                        type="number"
                        step="0.01"
                        min="0"
                        max="999.99"
                        v-model="form.amount"
                        class="money-input"
                        :disabled="form.busy"
                    />
                    <input-help :form="form" field="amount" text="" />
                </b-form-group>
            </b-col>
            <b-col lg="6">
                <business-location-form-group
                    v-model="form.business_id"
                    label="Office Location"
                    :allow-all="false"
                    :disabled="form.busy"
                    :required="true"
                />

                <b-form-group label="Payer" label-for="payer_id">
                    <b-select name="payer_id" id="payer_id" v-model="form.payer_id" :disabled="form.busy">
                        <option :value="null">-- Select a Payer --</option>
                        <option v-for="payer in payers" :key="payer.id" :value="payer.id">{{ payer.name }}</option>
                        <template slot="first">
                        </template>
                    </b-select>
                    <input-help :form="form" field="payer_id" text=""></input-help>
                </b-form-group>

                <b-form-group label="Notes" label-for="notes">
                    <b-textarea
                        v-model="form.notes"
                        id="notes"
                        name="notes"
                        type="text"
                        :disabled="form.busy"
                        rows="2"
                    />
                    <input-help :form="form" field="notes" text="" />
                </b-form-group>
            </b-col>
        </b-row>

        <hr />
        <div class="d-flex">
            <div class="ml-auto">
                <b-btn variant="success" @click="save()" :disabled="form.busy">
                    <span v-if="form.busy"><i class="fa fa-spin fa-spinner"></i></span>
                    <span v-else>{{ saveButtonTitle }}</span>
                </b-btn>
                <b-btn variant="default" @click="cancel()" :disabled="form.busy">Cancel</b-btn>
            </div>
        </div>
    </div>
</template>

<script>
    import BusinessLocationFormGroup from '../../../components/business/BusinessLocationFormGroup';
    import Constants from "../../../mixins/Constants";
    import FormatsNumbers from "../../../mixins/FormatsNumbers";
    import FormatsDates from "../../../mixins/FormatsDates";
    import { mapGetters } from 'vuex';
    import AuthUser from '../../../mixins/AuthUser';

    export default {
        components: {BusinessLocationFormGroup},
        mixins: [ Constants, FormatsNumbers, FormatsDates, AuthUser ],
        props: {
            remit: {
                type: Object,
                default: () => {},
            },
            payers: {
                type: Array,
                default: () => [],
                required: true,
            },
        },

        data() {
            return {
                form: new Form({
                    id: '',
                    business_id: '',
                    date: moment().format('MM/DD/YYYY'),
                    payment_type: '',
                    payer_id: null,
                    reference: '',
                    amount: 0.00,
                    notes: '',
                }),
            };
        },

        computed: {
            saveButtonTitle() {
                return this.remit.id ? 'Save Changes' : 'Add Remit';
            }
        },

        methods: {
            save() {
                if (this.remit.id) {
                    this.form.patch(`/business/claim-remits/${this.remit.id}`)
                        .then( ({ data }) => {
                            // this.$store.commit('claims/setRemit', data.data);
                            // TODO: handle update of remit
                            this.$emit('updated', data.data);
                            this.$emit('close');
                        })
                        .catch(() => {});
                } else {
                    this.form.post(`/business/claim-remits`)
                        .then( ({ data }) => {
                            // this.$store.commit('claims/setClaim', data.data);
                            // TODO: handle add update of remit
                            this.$emit('added', data.data);
                            this.$emit('close');
                        })
                        .catch(() => {});
                }
            },

            cancel() {
                this.$emit('close');
            },

            getDefaultBusinessId() {
                if (this.officeUserSettings) {
                    return this.officeUserSettings.default_business_id;
                }

                return this.$store.getters.defaultBusiness.id || '';
            }
        },

        watch: {
            remit(val) {
                if (val.id) {
                    this.form.fill({
                        ...val,
                        date: this.formatDate(val.date),
                    });
                } else {
                    this.form.reset(true);
                    this.form.business_id = this.getDefaultBusinessId();
                    this.form.id = null;
                    this.form.payment_type = this.CLAIM_REMIT_TYPES.REMIT;
                }
            }
        },
    }
</script>
