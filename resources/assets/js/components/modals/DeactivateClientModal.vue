<template>
    <b-modal id="deactivateModal"
             title="Are you sure?"
             v-model="deactivateModal"
             ok-title="OK">

        <b-container>
            <b-row>
                <b-col lg="12" class="text-center">
                    <div class="mb-3">Are you sure you wish to archive {{ this.client.name }}?</div>
                    <div v-if="client.future_schedules > 0">All <span class="text-danger">{{ this.client.future_schedules }}</span> of their future scheduled shifts will be deleted.</div>
                    <div v-else>They have no future scheduled shifts.</div>

                    <b-form-group label-for="inactive_at" class="mt-4">
                        <date-picker
                            class="w-50 mx-auto"
                            v-model="inactive_at"
                            id="inactive_at"
                            placeholder="Inactive Date">
                        </date-picker>
                        <input-help field="inactive_at" text="Set a deactivated date (optional)"></input-help>
                    </b-form-group>

                    <b-form-group label="Reason for Deactivation">
                        <b-form-select v-model="deactivation_reason_id" class="w-50 mx-auto">
                            <option v-for="reason in defaultBusiness.clientDeactivationReasons" :value="reason.id">
                                {{ reason.name }}
                            </option>
                        </b-form-select>
                    </b-form-group>

                    <b-form-group label-for="reactivation_date" class="mt-4">
                        <date-picker
                            class="w-50 mx-auto"
                            v-model="reactivation_date"
                            id="reactivation_date"
                            placeholder="Reactivation Date">
                        </date-picker>
                        <input-help field="reactivation_date" text="Set an automatic reactivation date (optional)"></input-help>
                    </b-form-group>

                </b-col>
            </b-row>
        </b-container>
        <div slot="modal-footer">
            <b-btn v-if="client.future_schedules > 0" variant="danger" class="mr-2" @click.prevent="archiveClient">Yes - Delete Future Schedules</b-btn>
            <b-btn v-else variant="danger" class="mr-2" @click.prevent="archiveClient">Yes</b-btn>
            <b-btn variant="default" @click="deactivateModal = false">Cancel</b-btn>
        </div>
    </b-modal>
</template>

<script>
    import { mapGetters } from 'vuex';

    export default {
        props: {
            client: {
                type: Object,
                required: true
            }
        },

        mixins: [],

        components: {},

        data () {
            return {
                deactivateModal: false,
                deactivation_reason_id: '',
                reactivation_date: '',
                inactive_at: '',
            }
        },

        created () {
        },

        mounted () {
        },

        computed: {
            ...mapGetters(['defaultBusiness'])
        },

        methods: {
            show () {
                this.deactivateModal = true;
            },

            hide () {
                this.deactivateModal = false;
            },

            archiveClient() {
                let form = new Form();
                let url = `/business/clients/${this.client.id}?inactive_at=${this.inactive_at}&deactivation_reason_id=${this.deactivation_reason_id}&reactivation_date=${this.reactivation_date}`;
                form.submit('delete', url);
            },
        }
    }
</script>

<style lang="scss">
</style>
