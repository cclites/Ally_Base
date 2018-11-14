<template>
    <div>

    <b-modal id="clientCaregiverModal" :title="modalTitle" v-model="value">
        <b-container fluid>
            <b-row>
                <b-col lg="12">
                    <div v-if="!caregiver.id">
                        <b-form-group label="Caregiver" label-for="caregiver_id">
                            <select2
                                    v-model="form.caregiver_id"
                                    class="form-control"
                            >
                                <option value="">-- Select Caregiver --</option>
                                <option v-for="item in caregiverList" :value="item.id" :key="item.id">{{ item.name }}</option>
                            </select2>
                            <input-help :form="form" field="caregiver_id" text=""></input-help>
                        </b-form-group>
                    </div>
                    <b-tabs v-if="form.caregiver_id">
                        <b-tab title="Hourly Rates" active>
                            <b-form-group label="Override Client Rate: " label-for="client_hourly_id">
                                <b-select v-model="form.client_hourly_id" class="ml-1 mr-2">
                                    <option value="">--Use Default--</option>
                                    <option v-for="code in clientHourlyRateCodes" :value="code.id" :key="code.id">{{ code.name }}</option>
                                </b-select>
                                <input-help :form="form" field="client_hourly_id"></input-help>
                            </b-form-group>
                            <b-form-group label="Override Caregiver Rate: " label-for="caregiver_hourly_id">
                                <b-select v-model="form.caregiver_hourly_id" class="ml-1 mr-2">
                                    <option value="">--Use Default--</option>
                                    <option v-for="code in caregiverHourlyRateCodes" :value="code.id" :key="code.id">{{ code.name }}</option>
                                </b-select>
                                <input-help :form="form" field="caregiver_hourly_id"></input-help>
                            </b-form-group>
                        </b-tab>
                        <b-tab title="Fixed Rates (Per-Shift)">
                            <b-form-group label="Override Client Rate: " label-for="client_fixed_id">
                                <b-select v-model="form.client_fixed_id" class="ml-1 mr-2">
                                    <option value="">--Use Default--</option>
                                    <option v-for="code in clientFixedRateCodes" :value="code.id" :key="code.id">{{ code.name }}</option>
                                </b-select>
                                <input-help :form="form" field="client_fixed_id"></input-help>
                            </b-form-group>
                            <b-form-group label="Override Caregiver Rate: " label-for="caregiver_fixed_id">
                                <b-select v-model="form.caregiver_fixed_id" class="ml-1 mr-2">
                                    <option value="">--Use Default--</option>
                                    <option v-for="code in caregiverFixedRateCodes" :value="code.id" :key="code.id">{{ code.name }}</option>
                                </b-select>
                                <input-help :form="form" field="caregiver_fixed_id"></input-help>
                            </b-form-group>
                        </b-tab>
                    </b-tabs>
                </b-col>
            </b-row>
        </b-container>
        <div slot="modal-footer">
            <b-btn variant="default" @click="value=false">Close</b-btn>
            <b-btn variant="info" @click="save()" v-if="form.caregiver_id">Save</b-btn>
        </div>
    </b-modal>

    </div>
</template>

<script>
    import RateCodes from "../../../mixins/RateCodes";

    export default {
        name: "ClientCaregiverRateCodeModal",

        mixins: [RateCodes],

        props: {
            rateStructure: {
                type: String,
                required: true,
            },
            client: {
                type: Object,
                required: true,
            },
            caregiver: {
                type: Object,
                required: true,
            },
            pivot: {
                type: Object,
                required: true,
            },
            caregiverList: {
                type: Array,
                required: true,
            },
            value: {
                type: Boolean,
            },
        },

        data() {
            return {
                form: this.makeForm(),
            }
        },


        computed: {
            modalTitle() {
                return this.caregiver.id ? 'Edit Caregiver Assignment' : 'Add Caregiver Assignment';
            },
        },

        methods: {
            makeForm() {
                return new Form({
                    caregiver_id: this.caregiver.id || "",
                    caregiver_hourly_id: this.pivot.caregiver_hourly_id || "",
                    caregiver_fixed_id: this.pivot.caregiver_fixed_id || "",
                    client_hourly_id: this.pivot.client_hourly_id || "",
                    client_fixed_id: this.pivot.client_fixed_id || "",
                    provider_fixed_id: this.pivot.provider_fixed_id || "",
                    provider_hourly_id: this.pivot.provider_hourly_id || "",
                    caregiver_hourly_rate: null,
                    caregiver_fixed_rate: null,
                    client_hourly_rate: null,
                    client_fixed_rate: null,
                    provider_hourly_fee: null,
                    provider_fixed_fee: null,
                });
            },

            async save() {
                const response = await this.form.post('/business/clients/' + this.client.id + '/caregivers')
                this.$emit('saved', response.data.data);
            }
        },

        mounted() {
            this.fetchRateCodes();
        },

        watch: {
            value(val) {
                if (val) {
                    this.fetchRateCodes();
                    console.log(this.makeForm());
                    this.form = this.makeForm();
                }
            }
        }
    }
</script>

<style scoped>

</style>
