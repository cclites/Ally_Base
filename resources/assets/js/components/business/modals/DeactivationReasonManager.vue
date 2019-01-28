<template>
    <b-modal ref="deactivationReasonModal" :title="`Add ${label}`" @ok="addReason" @cancel="hide()" ok-variant="info">
        <b-form-group :label="label">
            <b-form-input v-model="form.name"></b-form-input>
        </b-form-group>

    </b-modal>
</template>

<script>
    import { mapGetters, mapMutations } from 'vuex'

    export default {
        name: 'deactivation-reason-manager',

        mixins: [],

        components: {},

        data() {
            return {
                form: new Form({
                    business_id: 0,
                    name: '',
                    type: ''
                })
            }
        },

        computed: {
            business() {
                return this.$parent.business;
            },

            label() {
                return _.upperFirst(this.form.type) + ' Deactivation Reason'
            }
        },

        methods: {
            ...mapGetters(['defaultBusiness']),

            async addReason() {
                this.form.business_id = this.business.id;
                let response = await this.form.post('/business/settings/deactivation-reasons');
                this.$emit('reasonAdded', response.data);
            },

            show(type) {
                this.form.type = type;
                this.name = '';
                this.$refs.deactivationReasonModal.show()
            },

            hide() {
                this.form.reason = '';
                this.$refs.deactivationReasonModal.hide()
            }
        }
    }
</script>
