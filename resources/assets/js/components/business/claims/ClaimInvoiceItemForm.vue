<template>
    <div>
        <!-- Expenses -->
        <div v-if="item.claimable_type == 'App\\ClaimableExpense'">
            <b-row>
                <b-col lg="6">
                    <b-form-group label="Claimable Type" label-for="type" class="bold">
                        <label>{{ item.type }}</label>
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Related Shift" label-for="shift_id" class="bold">
                        <label v-if="item.related_shift_id">
                            <a :href="`/business/shifts/${item.related_shift_id}`" target="_blank">{{ item.related_shift_id }}</a>
                        </label>
                        <label v-else>-</label>
                    </b-form-group>
                </b-col>
            </b-row>
<!--            <b-row>-->
<!--                <b-col lg="6">-->
<!--                    <b-form-group label="Amount" label-for="amount" class="bold">-->
<!--                        <label>{{ moneyFormat(item.amount) }}</label>-->
<!--                    </b-form-group>-->
<!--                </b-col>-->
<!--                <b-col lg="6">-->
<!--                    <b-form-group label="Amount Due" label-for="amount_due" class="bold">-->
<!--                        <label>{{ moneyFormat(item.amount_due) }}</label>-->
<!--                    </b-form-group>-->
<!--                </b-col>-->
<!--            </b-row>-->
            <b-row>
                <b-col sm="4">
                    <b-form-group label="Name" label-for="name">
                        <b-form-input
                            v-model="form.name"
                            id="name"
                            name="name"
                            type="text"
                            :disabled="form.busy"
                        />
                        <input-help :form="form" field="name" text=""></input-help>
                    </b-form-group>
                </b-col>
                <b-col sm="2">
                    <b-form-group label="Date" label-for="date">
                        <mask-input v-model="form.date" id="date" type="date" :disabled="form.busy"></mask-input>
                        <input-help :form="form" field="date" text=""></input-help>
                    </b-form-group>
                </b-col>
                <b-col sm="2">
                    <b-form-group label="Rate" label-for="rate">
                        <b-form-input
                            name="rate"
                            type="number"
                            step="0.01"
                            min="0"
                            max="999.99"
                            v-model="form.rate"
                            class="money-input"
                            @change="recalculateAmount()"
                            :disabled="form.busy"
                        />
                        <input-help :form="form" field="rate" text=""></input-help>
                    </b-form-group>
                </b-col>
                <b-col sm="2">
                    <b-form-group label="Units" label-for="units">
                        <b-form-input
                            name="units"
                            type="number"
                            step="0.01"
                            min="0"
                            max="999.99"
                            v-model="form.units"
                            class="money-input"
                            @change="recalculateAmount()"
                            :disabled="form.busy"
                        />
                        <input-help :form="form" field="units" text=""></input-help>
                    </b-form-group>
                </b-col>
                <b-col sm="2">
                    <b-form-group label="Amount" label-for="amount">
                        <b-form-input
                            name="amount"
                            type="number"
                            step="0.01"
                            min="0"
                            max="999.99"
                            v-model="form.amount"
                            @change="recalculateRate()"
                            class="money-input"
                            :disabled="form.busy"
                        />
                        <input-help :form="form" field="amount" text=""></input-help>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="12">
                    <b-form-group label="Notes" label-for="notes">
                        <b-textarea
                            id="notes"
                            name="notes"
                            :rows="2"
                            v-model="form.notes"
                            :disabled="form.busy"
                        />
                        <input-help :form="form" field="notes" text=""></input-help>
                    </b-form-group>
                </b-col>
            </b-row>
        </div>

        <!-- Services -->
        <div v-else>
            <b-row>
                <b-col lg="6">
                </b-col>
                <b-col lg="6">
                </b-col>
            </b-row>
        </div>

        <hr />
        <div class="d-flex">
            <div class="ml-auto">
                <b-btn variant="success" @click="save()" :disabled="form.busy">
                    <span v-if="form.busy"><i class="fa fa-spin fa-spinner"></i></span>
                    <span v-else>{{ saveButtonTitle }}</span>
                </b-btn>
                <b-btn variant="default" @click="cancel()" :disabled="form.busy">Close</b-btn>
            </div>
        </div>
    </div>
</template>

<script>
    import { Decimal } from 'decimal.js';
    import Constants from "../../../mixins/Constants";
    import FormatsNumbers from "../../../mixins/FormatsNumbers";
    import FormatsDates from "../../../mixins/FormatsDates";
    import { mapGetters } from 'vuex';

    export default {
        mixins: [ Constants, FormatsNumbers, FormatsDates ],
        props: {
            item: {
                type: Object,
                default: () => {},
            },
        },

        data() {
            return {
                form: new Form({
                    claimable_type: '',
                    name: '',
                    rate: 0.00,
                    units: 0,
                    amount: 0.00,
                    notes: '',
                    date: '',
                }),
            };
        },

        computed: {
            saveButtonTitle() {
                return this.item.id ? 'Save Changes' : 'Create Item';
            }
        },

        methods: {
            save() {
                this.form.patch(`/business/claims/${this.item.claim_invoice_id}/item/${this.item.id}`)
                    .then( ({ data }) => {
                        this.$store.commit('claims/setClaim', data.data);
                        this.$emit('close');
                    })
                    .catch(() => {});
            },

            cancel() {
                this.$emit('close');
            },

            recalculateAmount() {
                let rate = new Decimal(this.form.rate || 0);
                let units = new Decimal(this.form.units || 0);
                this.form.amount = rate.times(units).toFixed(2);
            },

            recalculateRate() {
                let amount = new Decimal(this.form.amount || 0);
                let units = new Decimal(this.form.units || 0);

                if (amount === new Decimal(0)) {
                    this.form.rate = 0.00;
                    return;
                }

                this.form.rate = amount.dividedBy(units).toFixed(2);
            },
        },

        watch: {
            item(val) {
                console.log('item changed');
                if (val.id) {
                    this.form.fill({
                        ...val,
                        ...val.claimable,
                        date: this.formatDate(val.claimable.date),
                    });
                } else {
                    this.form.reset(true);
                }
            }
        },
    }
</script>
