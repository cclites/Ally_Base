<template>
    <div>
        <b-list-group>
            <b-list-group-item v-for="reason in reasons" :key="reason.id" class="d-flex">
                <div class="f-1">{{ reason.name }}</div>
                <div class="ml-auto">
                    <!-- <i class="fa fa-trash-alt"></i> -->
                    <a v-if="reason.business_id" href="#" @click.prevent="remove(reason)"><i class="fa fa-trash"></i></a>
                </div>
            </b-list-group-item>
            <b-list-group-item button @click="show()">
                <i class="fa fa-plus mr-2"></i>Add Reason
            </b-list-group-item>
        </b-list-group>

        <b-modal ref="deactivationReasonModal" :title="`Add ${label}`" @ok="addReason" @cancel="hide()" ok-variant="info">
            <b-form-group :label="label">
                <b-form-input v-model="form.name"></b-form-input>
            </b-form-group>
        </b-modal>
    </div>
</template>

<script>
    import {  mapMutations } from 'vuex'

    export default {
        name: 'deactivation-reason-manager',

        props: {
            business: {
                type: Object,
                default: () => { return {} },
            },
            type: {
                type: String,
                default: 'client',
            }
        },

        data() {
            return {
                form: new Form({
                    business_id: 0,
                    name: '',
                    type: ''
                }),
                deactivationReasons: {
                    client: [],
                    caregiver: []
                },
            }
        },

        computed: {
            label() {
                return _.upperFirst(this.form.type) + ' Deactivation Reason'
            },

            reasons() {
                if (this.type === 'caregiver') {
                    return this.business.caregiverDeactivationReasons;
                }

                return this.business.clientDeactivationReasons;
            }
        },

        methods: {
            ...mapMutations(['updateBusiness']),

            async addReason() {
                this.form.post('/business/settings/deactivation-reasons')
                    .then( ({ data }) => {
                        this.updateReasons(data.data);
                    })
                    .catch(e => {

                    })
            },

            show() {
                this.form.business_id = this.business.id;
                this.form.type = this.type;
                this.form.name = '';
                this.$refs.deactivationReasonModal.show()
            },

            hide() {
                this.form.reason = '';
                this.$refs.deactivationReasonModal.hide()
            },

            updateReasons(item) {
                let business = JSON.parse(JSON.stringify(this.business));
                switch (item.type) {
                    case 'caregiver':
                        business.caregiverDeactivationReasons.push(item);
                        this.updateBusiness(business);
                        break;
                    case 'client':
                        business.clientDeactivationReasons.push(item);
                        this.updateBusiness(business);
                        break;
                }
            },

            removeReasonFormBusiness(reason) {
                let business = JSON.parse(JSON.stringify(this.business));
                let index = -1;

                switch (reason.type) {
                    case 'caregiver':
                        index = business.caregiverDeactivationReasons.findIndex(x => x.id == reason.id);
                        if (index >= 0) {
                            business.caregiverDeactivationReasons.splice(index, 1);
                            this.updateBusiness(business);
                        }
                        break;
                    case 'client':
                        index = business.clientDeactivationReasons.findIndex(x => x.id == reason.id);
                        if (index >= 0) {
                            business.clientDeactivationReasons.splice(index, 1);
                            this.updateBusiness(business);
                        }
                        break;
                }
            },

            remove(reason) {
                if (! confirm('Are you sure you want to remove this deactivation reason code?')) {
                    return;
                }

                let form = new Form({});
                form.submit('DELETE', `/business/settings/deactivation-reasons/${reason.id}`)
                    .then(() => {
                        // delete item
                        this.removeReasonFormBusiness(reason);
                    })
                    .catch(e => {
                    })
            },
        },

        mounted() {
            this.deactivationReasons = {
                client: this.business ? this.business.clientDeactivationReasons : [],
                caregiver: this.business ? this.business.caregiverDeactivationReasons : []
            };
        },
    }
</script>
