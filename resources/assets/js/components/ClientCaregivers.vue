<template>
    <b-card
        header="Caregivers"
        header-text-variant="white"
        header-bg-variant="info"
        >
        <b-row>
            <b-col sm="6">
                <b-btn variant="info" @click="addCaregiver()">Add Caregiver to Client</b-btn>
            </b-col>
            <b-col sm="6" class="text-right">
                {{ paymentTypeMessage }}
            </b-col>
        </b-row>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                <tr class="top-row">
                    <th rowspan="2">Assigned Caregiver</th>
                    <th colspan="4" class="text-center hourly">Hourly</th>
                    <!-- <th colspan="4" class="text-center daily">Daily</th> -->
                    <th rowspan="2"></th>
                </tr>
                <tr>
                    <th class="hourly">Caregiver Rate</th>
                    <th class="hourly">Provider Fee</th>
                    <th class="hourly">Ally Fee</th>
                    <th class="hourly">Total</th>
                    <!-- <th class="daily">Caregiver Rate</th> -->
                    <!-- <th class="daily">Provider Fee</th> -->
                    <!-- <th class="daily">Ally Fee</th> -->
                </tr>
                </thead>
                <tbody>
                <tr v-for="item in items">
                    <td>{{ item.firstname }} {{ item.lastname }}</td>
                    <td class="hourly">{{ item.pivot.caregiver_hourly_rate }}</td>
                    <td class="hourly">{{ item.pivot.provider_hourly_fee }}</td>
                    <td class="hourly">{{ item.pivot.ally_hourly_fee }}</td>
                    <td class="hourly">{{ item.pivot.total_hourly_fee }}</td>
                    <!-- <td class="daily">{{ item.pivot.caregiver_daily_rate }}</td> -->
                    <!-- <td class="daily">{{ item.pivot.provider_daily_fee }}</td> -->
                    <!--<td class="hourly">{{ item.pivot.ally_daily_fee }}</td>-->
                    <!--<td class="daily">{{ item.pivot.total_daily_fee }}</td>-->
                    <td>
                        <b-btn size="sm" @click="editCaregiver(item)">Edit</b-btn>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <b-modal id="clientCaregiverModal" :title="modalTitle" v-model="clientCaregiverModal">
            <b-container fluid>
                <b-row v-if="!selectedCaregiver.id">
                    <b-col lg="12">
                        <b-form-group label="Caregiver" label-for="caregiver_id">
                            <b-form-select
                                id="caregiver_id"
                                name="caregiver_id"
                                v-model="form.caregiver_id"
                                >
                                <option v-for="item in list" :value="item.id">{{ item.name }}</option>
                            </b-form-select>
                            <input-help :form="form" field="caregiver_id" text=""></input-help>
                        </b-form-group>
                    </b-col>
                </b-row>
                <b-row v-if="form.caregiver_id">
                    <b-col lg="12">
                        <b-form-group label="Caregiver Hourly Rate" label-for="caregiver_hourly_rate">
                            <b-form-input
                                id="caregiver_hourly_rate"
                                name="caregiver_hourly_rate"
                                type="number"
                                v-model="form.caregiver_hourly_rate"
                                >
                            </b-form-input>
                            <input-help :form="form" field="caregiver_hourly_rate" text="Enter the hourly earnings for this caregiver."></input-help>
                        </b-form-group>
                        <b-form-group label="Provider Hourly Fee" label-for="provider_hourly_fee">
                            <b-form-input
                                    id="provider_hourly_fee"
                                    name="provider_hourly_fee"
                                    type="number"
                                    v-model="form.provider_hourly_fee"
                            >
                            </b-form-input>
                            <input-help :form="form" field="provider_hourly_fee" text="Enter the provider referral fee for hourly earnings."></input-help>
                        </b-form-group>
                        <!-- <b-form-group label="Caregiver Daily Rate" label-for="caregiver_daily_rate">
                            <b-form-input
                                    id="caregiver_daily_rate"
                                    name="caregiver_daily_rate"
                                    type="number"
                                    v-model="form.caregiver_daily_rate"
                            >
                            </b-form-input>
                            <input-help :form="form" field="caregiver_daily_rate" text="Enter the daily earnings for this caregiver. (All day shifts)"></input-help>
                        </b-form-group>
                        <b-form-group label="Provider Daily Fee" label-for="provider_daily_fee">
                            <b-form-input
                                    id="provider_daily_fee"
                                    name="provider_daily_fee"
                                    type="number"
                                    v-model="form.provider_daily_fee"
                            >
                            </b-form-input>
                            <input-help :form="form" field="provider_daily_fee" text="Enter the provider referral fee for daily shifts."></input-help>
                        </b-form-group> -->
                    </b-col>
               </b-row>
            </b-container>
            <div slot="modal-footer">
               <b-btn variant="default" @click="clientCaregiverModal=false">Close</b-btn>
               <b-btn variant="info" @click="saveCaregiver()" v-if="form.caregiver_id">Save</b-btn>
            </div>
        </b-modal>

    </b-card>
</template>

<script>
    export default {
        props: {
            'list': {},
            'client_id': {},
            'paymentTypeMessage': {
                default() {
                    return '';
                }
            }
        },

        data() {
            return {
                items: {},
                clientCaregiverModal: false,
                selectedCaregiver: {},
                form: new Form(),
            }
        },

        mounted() {
            axios.get('/business/clients/' + this.client_id + '/caregivers')
                .then(response => {
                    if (Array.isArray(response.data)) {
                        this.items = response.data;
                    }
                    else {
                        this.items = [];
                    }
                });
        },

        methods: {
            addCaregiver() {
                this.selectedCaregiver = {};
                this.form = new Form({
                    caregiver_id: null,
                    caregiver_hourly_rate: null,
                    caregiver_daily_rate: null,
                    provider_hourly_fee: null,
                    provider_daily_fee: null,
                });
                this.clientCaregiverModal = true;
            },
            editCaregiver(item) {
                this.selectedCaregiver = item;
                this.form = new Form({
                    caregiver_id: item.id,
                    caregiver_hourly_rate: item.pivot.caregiver_hourly_rate,
                    caregiver_daily_rate: item.pivot.caregiver_daily_rate,
                    provider_hourly_fee: item.pivot.provider_hourly_fee,
                    provider_daily_fee: item.pivot.provider_daily_fee,
                });
                this.clientCaregiverModal = true;
            },
            saveCaregiver() {
                let component = this;
                this.form.post('/business/clients/' + component.client_id + '/caregivers')
                    .then(function(response) {
                        component.items = component.items.filter(caregiver => {
                            return caregiver.id != response.data.data.id;
                        });
                        component.items.unshift(response.data.data);
                        component.clientCaregiverModal = false;
                    })
            }
        },

        computed: {
            modalTitle() {
                if (this.selectedCaregiver.id) {
                    return 'Edit Caregiver Assignment';
                }
                return 'Add Caregiver Assignment';
            }
        }
    }
</script>

<style>
    th.hourly, td.hourly {
        background-color: #eee;
    }
    th.daily, td.daily {
        background-color: #f5f5f5;
    }
    thead th {
        padding: .4rem .75rem !important;
    }
    tr td.hourly, tr th.hourly {
        text-align: center;
    }
</style>