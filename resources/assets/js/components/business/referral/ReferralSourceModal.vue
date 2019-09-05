<template>
    <form @submit.prevent="submitForm()" @keydown="form.clearError($event.target.name)">
        <b-modal id="ReferralSourceModal" :title="title" v-model="showModal">
            <b-container fluid>
                <b-row>
                    <b-col lg="12">
                        <b-form-group label="Organization Name" label-for="organization" label-class="required">
                            <b-form-input v-model="form.organization" type="text" required />
                            <input-help :form="form" field="organization"></input-help>
                        </b-form-group>
                        <b-form-group label="Contact Name" label-for="name" label-class="required">
                            <b-form-input v-model="form.contact_name" type="text" required />
                            <input-help :form="form" field="contact_name"></input-help>
                        </b-form-group>
                        <b-form-group label="Phone Number" label-for="phone">
                            <b-form-input v-model="form.phone" type="text" />
                            <input-help :form="form" field="phone"></input-help>
                        </b-form-group>
                    </b-col>
                </b-row>
            </b-container>
            <div slot="modal-footer">
                <b-button variant="success"
                          type="submit"
                          :disabled="loading"
                >
                    {{ buttonText }}
                </b-button>
                <b-btn variant="default" @click="showModal=false">Close</b-btn>
            </div>
        </b-modal>
    </form>
</template>

<script>
    export default {
        props: {
            value: Boolean,
            source: '',
            sourceType: {
                type: String,
                default: 'client',
            }
        },

        data() {
            return {
                form: this.makeForm(this.source),
                loading: false,
                showModal: this.value,
            }
        },

        computed: {
            title() {
                return (this.source.id) ? 'Edit Referral Source' : 'Add New Referral Source';
            },
            buttonText() {
                return (this.source.id) ? 'Save' : 'Create';
            },
        },

        methods: {
            makeForm(defaults = {}) {
                return new Form({
                    organization: defaults.organization,
                    contact_name: defaults.contact_name,
                    phone: defaults.phone,
                    type: this.sourceType,
                });
            },

            submitForm() {
                this.loading = true;
                let method = this.source.id ? 'patch' : 'post';
                let url = this.source.id ? `/business/referral-sources/${this.source.id}` : '/business/referral-sources';
                this.form.submit(method, url)
                    .then(response => {
                        this.$emit('saved', response.data.data);
                        this.showModal = false;
                    })
                    .finally(() => this.loading = false)
            },
        },

        watch: {
            value(val) {
                this.form = this.makeForm(this.source);
                this.showModal = val;
            },
            showModal(val) {
                console.log("Show Modal val in Add is " + val);
                this.$emit('input', val);
            }
        }
    }
</script>

<style scoped>
    .loader {
        border: 8px solid #f3f3f3;
        border-radius: 50%;
        border-top: 8px solid #3498db;
        width: 50px;
        height: 50px;
        -webkit-animation: spin-data-v-7012acc5 2s linear infinite;
        animation: spin-data-v-7012acc5 2s linear infinite;
        margin: 0 auto;
    }

    /* Safari */
    @-webkit-keyframes spin {
        0% { -webkit-transform: rotate(0deg); }
        100% { -webkit-transform: rotate(360deg); }
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .error-msg {
        margin-top: 7px;
        color: red;
    }
</style>