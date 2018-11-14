<template>
    <b-modal ref="deactivationReasonModal" :title="`Add ${label}`" @ok="addReason" @cancel="hide()" ok-variant="info">
        <b-form-group :label="label">
            <b-form-input v-model="form.name"></b-form-input>
        </b-form-group>

    </b-modal>
</template>

<script>
    export default {
        name: 'deactivation-reason-manager',

        props: {
            business: {
                type: Object,
                required: true
            },
        },

        mixins: [],

        components: {},

        data() {
            return {
                form: new Form({
                    business_id: this.business.id,
                    name: '',
                    type: ''
                })
            }
        },

        created() {
        },

        mounted() {
        },

        computed: {
            label() {
                return _.upperFirst(this.form.type) + ' Deactivation Reason'
            }
        },

        methods: {
            async addReason() {
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
