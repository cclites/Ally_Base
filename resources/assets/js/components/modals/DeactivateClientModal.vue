<template>
    <b-modal id="deactivateModal"
             :title="`Are you sure you wish to archive ${client.name}?`"
             size="lg"
             v-model="deactivateModal">
        <b-container>
            <b-row>
                <b-col class="text-center">
                    <div v-if="client.future_schedules > 0">
                        All <span class="text-danger">{{ this.client.future_schedules }}</span>
                        of their future scheduled shifts will be deleted.
                    </div>
                    <div v-else class="text-success">
                        They have no future scheduled shifts.
                    </div>
                </b-col>
            </b-row>

            <b-row>
                <b-col class="text-center">
                    <div v-if="client.open_invoices > 0">This client has <span class="text-danger">{{ this.client.open_invoices }}</span>
                        open invoices.
                    </div>
                    <div v-else class="text-success">
                        They have no open invoices.
                    </div>
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="6">
                    <b-form-group label-for="inactive_at" class="mt-4">
                        <date-picker
                            v-model="form.inactive_at"
                            id="inactive_at"
                            placeholder="Inactive Date">
                        </date-picker>
                        <input-help :form="form" field="inactive_at"
                                    text="Set a deactivated date (optional)"></input-help>
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group label-for="reactivation_date" class="mt-4">
                        <date-picker
                            v-model="form.reactivation_date"
                            id="reactivation_date"
                            placeholder="Reactivation Date">
                        </date-picker>
                        <input-help :form="form" field="reactivation_date"
                                    text="Set an automatic reactivation date (optional)"></input-help>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="6">
                    <b-form-group label="Reason for Deactivation">
                        <b-form-select v-model="form.deactivation_reason_id">
                            <option v-for="reason in this.deactivationReasons.client" :key="reason.id" :value="reason.id">
                                {{ reason.name }}
                            </option>
                        </b-form-select>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row class="mt-2">
                <b-col>
                    <p class="m-0">Discharge Summary</p>
                </b-col>
            </b-row>
            <hr>
            <b-row>
                <b-col lg="6">
                    <b-form-group label="Discharge Reason">
                        <b-form-textarea :rows="3" v-model="form.discharge_reason"></b-form-textarea>
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Condition of Client">
                        <b-form-textarea :rows="3" v-model="form.discharge_condition"></b-form-textarea>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="6">
                    <b-form-group label="Evaluation of Goals">
                        <b-form-textarea :rows="3" v-model="form.discharge_goals_eval"></b-form-textarea>
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Disposition Notes">
                        <b-form-textarea :rows="3" v-model="form.discharge_disposition"></b-form-textarea>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row>
                <b-col>
                    <b-form-group label="Other D/C Notes (Internal Use)">
                        <b-form-textarea :rows="3" v-model="form.discharge_internal_notes"></b-form-textarea>
                    </b-form-group>
                </b-col>
            </b-row>
        </b-container>
        <div slot="modal-footer">
            <b-btn v-if="client.future_schedules > 0" variant="danger" class="mr-2" @click.prevent="archiveClient">Yes -
                Delete Future Schedules
            </b-btn>
            <b-btn v-else variant="danger" class="mr-2" @click.prevent="archiveClient">Archive {{ client.name }}</b-btn>
            <b-btn variant="default" @click="deactivateModal = false">Cancel</b-btn>
        </div>
    </b-modal>
</template>

<script>
    import {mapGetters} from 'vuex';

    export default {
        props: {
            client: {
                type: Object,
                required: true
            }
        },

        data () {
            return {
                form: new Form ({
                    active: false,
                    deactivation_reason_id: '',
                    reactivation_date: '',
                    inactive_at: '',
                    discharge_reason: '',
                    discharge_condition: '',
                    discharge_goals_eval: '',
                    discharge_disposition: '',
                    discharge_internal_notes: ''
                }),
                deactivationReasons: {
                    client: [],
                    caregiver: []
                },
                deactivateModal: false
            }
        },

        computed: {
        },

        methods: {
            show () {
                this.deactivateModal = true;
            },

            hide () {
                this.deactivateModal = false;
            },

            archiveClient () {

                if ( ! confirm('This will prevent any charges or deposits from being completed. Please be sure to check that there are no outstanding invoices or payments.')) {
                    return;
                }
                let url = `/business/clients/${this.client.id}/deactivate`;
                this.form.submit ('post', url);
            },
        },

        mounted() {
            axios.get(`/business/settings/deactivation-reasons?business_id=${this.client.business_id}`)
                .then( ({ data }) => {
                    this.deactivationReasons = data;
                });
        },
    }
</script>
