<template>
    <div>
        <b-row>
            <b-col>
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

                <b-form-group label="Adjustment Type" label-for="adjustment_type" label-class="required">
                    <b-select name="adjustment_type"
                        class="mr-1"
                        v-model="form.adjustment_type"
                        :options="claimAdjustmentTypeOptions"
                        :disabled="form.busy"
                    >
                        <template slot="first">
                            <option value="">-- Type --</option>
                        </template>
                    </b-select>
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
                    <span v-else>Create Adjustment</span>
                </b-btn>
                <b-btn variant="default" @click="cancel()" :disabled="form.busy">Cancel</b-btn>
            </div>
        </div>
    </div>
</template>

<script>
    import BusinessLocationFormGroup from '../../../components/business/BusinessLocationFormGroup';
    import FormatsNumbers from "../../../mixins/FormatsNumbers";
    import FormatsDates from "../../../mixins/FormatsDates";
    import Constants from "../../../mixins/Constants";
    import AuthUser from '../../../mixins/AuthUser';

    export default {
        components: {BusinessLocationFormGroup},
        mixins: [ Constants, FormatsNumbers, FormatsDates, AuthUser ],
        props: {
            remit: {
                type: Object,
                default: () => {},
            },
        },

        data() {
            return {
                form: new Form({
                    id: '',
                    business_id: '',
                    date: moment().format('MM/DD/YYYY'),
                    payment_type: '',
                    payer_id: '',
                    reference: '',
                    amount: 0.00,
                    notes: '',
                }),
            };
        },

        methods: {
            save() {
                this.form.post(`/business/claim-remits/${this.remit.id}/adjust`)
                    .then( ({ data }) => {
                        this.$emit('updated', data.data);
                        this.$emit('close');
                    })
                    .catch(() => {});
            },

            cancel() {
                this.$emit('close');
            },
        },

        watch: {
            remit(val) {
                this.form.fill({
                    ...val,
                    date: this.formatDate(val.date),
                });
            }
        },
    }
</script>
