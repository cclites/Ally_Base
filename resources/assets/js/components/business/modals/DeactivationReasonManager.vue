<template>
    <div>
        <b-list-group>
            <b-list-group-item v-for="reason in reasons" :key="reason.id" class="d-flex">
                <div class="f-1">{{ reason.name }}</div>
                <div class="ml-auto">
                    <!-- <i class="fa fa-trash-alt"></i> -->
                    <a v-if="reason.chain_id" href="#" @click.prevent="remove(reason)"><i class="fa fa-trash"></i></a>
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
            type: {
                type: String,
                default: 'client',
            }
        },

        data() {
            return {
                form: new Form({
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
                    return this.deactivationReasons.caregiver;
                }

                return this.deactivationReasons.client;
            }
        },

        methods: {
            async addReason() {
                this.form.post('/business/settings/deactivation-reasons')
                    .then( ({ data }) => {
                        this.updateReasons(data.data);
                    })
                    .catch(e => {
                    })
            },

            show() {
                this.form.type = this.type;
                this.form.name = '';
                this.$refs.deactivationReasonModal.show()
            },

            hide() {
                this.form.reason = '';
                this.$refs.deactivationReasonModal.hide()
            },

            updateReasons(item) {
                switch (item.type) {
                    case 'caregiver':
                        this.deactivationReasons.caregiver.push(item);
                        break;
                    case 'client':
                        this.deactivationReasons.client.push(item);
                        break;
                }
            },

            removeReason(reason) {
                let index = -1;

                switch (reason.type) {
                    case 'caregiver':
                        index = this.deactivationReasons.caregiver.findIndex(x => x.id == reason.id);
                        if (index >= 0) {
                            this.deactivationReasons.caregiver.splice(index, 1);
                        }
                        break;
                    case 'client':
                        index = this.deactivationReasons.client.findIndex(x => x.id == reason.id);
                        if (index >= 0) {
                            this.deactivationReasons.client.splice(index, 1);
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
                        this.removeReason(reason);
                    })
                    .catch(e => {
                    })
            },
        },

        mounted() {
            axios.get('/business/settings/deactivation-reasons')
                .then( ({ data }) => {
                    this.deactivationReasons = data;        
                });
        },
    }
</script>
