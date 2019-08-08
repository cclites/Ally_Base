<template>
    <b-modal id="deactivateModal"
             :title="`Are you sure you wish to archive ${this.caregiver.name}?`"
             v-model="deactivateModal">
        <b-container fluid>
            <b-row>
                <b-col lg="12" class="text-center">
                    <div v-if="caregiver.future_schedules > 0">All <span class="text-danger">{{ this.caregiver.future_schedules }}</span>
                        of their future scheduled shifts will be unassigned.
                    </div>
                    <div v-else class="text-info">They have no future scheduled shifts.</div>

                    <b-form-group slabel-for="inactive_at" class="mt-4">
                        <date-picker
                            v-model="inactive_at"
                            id="inactive_at"
                            placeholder="Inactive Date">
                        </date-picker>
                        <input-help :form="form" field="inactive_at"
                                    text="Set a deactivated date (optional)"></input-help>
                    </b-form-group>

                    <b-form-group label="Reason for Deactivation">
                        <b-form-select v-model="deactivation_reason_id" id="deactivation_reason_id">
                            <option v-for="reason in this.deactivationReasons.caregiver" :key="reason.id" :value="reason.id">
                                {{ reason.name }}
                            </option>
                        </b-form-select>
                        <input-help :form="form" field="deactivation_reason_id"
                                text="Discharge Summary/Reason for deactivation will be added to the documents tab of caregiver profile"></input-help>
                    </b-form-group>

                    <b-form-group label="Notes">
                        <b-form-textarea v-model="deactivation_note" :rows="3"></b-form-textarea>
                    </b-form-group>
                </b-col>
            </b-row>
        </b-container>
        <div slot="modal-footer">
            <b-btn v-if="caregiver.future_schedules > 0" variant="danger" class="mr-2"
                   @click.prevent="archiveCaregiver">Yes - Unassign Future Schedules
            </b-btn>
            <b-btn v-else variant="danger" class="mr-2" @click.prevent="archiveCaregiver">Deactivate {{ caregiver.name }}</b-btn>
            <b-btn variant="default" @click="deactivateModal = false">Cancel</b-btn>
        </div>
    </b-modal>
</template>

<script>

    import {mapGetters} from 'vuex';

    export default {
        props: {
            caregiver: {
                type: Object,
                required: true
            }
        },

        mixins: [],

        components: {},

        data () {
            return {
                form: new Form (),
                deactivateModal: false,
                deactivation_reason_id: '',
                inactive_at: '',
                deactivation_note: '',
                deactivationReasons: {
                    client: [],
                    caregiver: []
                },
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

            archiveCaregiver () {
                let form = new Form ();
                let url = `/business/caregivers/${this.caregiver.id}?inactive_at=${this.inactive_at}&deactivation_reason_id=${this.deactivation_reason_id}&note=${this.deactivation_note}`;
                form.submit ('delete', url);
            }
        },

        mounted() {
            axios.get('/business/settings/deactivation-reasons')
                .then( ({ data }) => {
                    this.deactivationReasons = data;        
                });
        },
    }
</script>
