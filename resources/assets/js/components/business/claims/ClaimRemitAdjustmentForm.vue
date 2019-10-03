<template>
    <div>
        <b-row>
            <b-col>
                <b-form-group label="Amount" label-for="amount_applied" label-class="required">
                    <b-form-input
                        id="amount_applied"
                        name="amount_applied"
                        type="number"
                        step="0.01"
                        min="-9999999.99"
                        max="9999999.99"
                        v-model="form.amount_applied"
                        class="money-input"
                        :disabled="form.busy"
                    />
                    <input-help :form="form" field="amount_applied" text="" />
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

                <b-form-group label="Notes" label-for="note">
                    <b-textarea
                        v-model="form.note"
                        id="note"
                        name="note"
                        type="text"
                        :disabled="form.busy"
                        rows="2"
                    />
                    <input-help :form="form" field="note" text="" />
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
                    amount_applied: 0.00,
                    adjustment_type: '',
                    note: '',
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
                    amount_applied: 0.00,
                    adjustment_type: '',
                    note: '',
                });
            }
        },
    }
</script>
