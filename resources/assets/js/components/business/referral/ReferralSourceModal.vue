<template>
    <form @submit.prevent="submitForm()" @keydown="form.clearError($event.target.name)">
        <b-modal id="ReferralSourceModal" :title="title" v-model="showModal">
            <b-container fluid>
                <b-row>
                    <b-col lg="12">
                        <b-form-group>
                            <b-form-checkbox v-if="!form.id" v-model=" form.is_company " class="align-items-center">
                                This is a Company: <b>{{ form.is_company }}</b>
                            </b-form-checkbox>
                        </b-form-group>

                        <transition-group mode="out-in" name="slide-fade">
                            <b-form-group v-if=" form.is_company " label="Organization Name" label-for="organization" label-class="required" key="seven">
                                <b-form-input v-model="form.organization" type="text" />
                                <input-help :form="form" field="organization"></input-help>
                            </b-form-group>

                            <b-form-group v-if=" !form.id " label="Contact Name" label-for="name" label-class="required" key="six">
                                <b-form-input v-model="form.contact_name" type="text" />
                                <input-help :form="form" field="contact_name"></input-help>
                            </b-form-group>

                            <b-form-group v-if=" !form.id " label="Phone Number" label-for="phone" label-class="required" :key=" 'five' ">
                                <mask-input v-model="form.phone" type="phone" />
                                <input-help :form="form" field="phone"></input-help>
                            </b-form-group>
                            <b-form-group key="address_street" label="Contact Address">
                                <b-row>
                                    <b-col lg="8">
                                        <b-form-group label="Street" label-for="contact_address_street" label-class="required" key="address_street">
                                            <b-form-input v-model="form.contact_address_street" type="text" />
                                            <input-help :form="form" field="contact_address_street"></input-help>
                                        </b-form-group>
                                    </b-col>
                                    <b-col lg="4">
                                        <b-form-group label="Street Line 2" label-for="contact_address_street2" key="address_street2">
                                            <b-form-input v-model="form.contact_address_street2" type="text" />
                                            <input-help :form="form" field="contact_address_street2"></input-help>
                                        </b-form-group>
                                    </b-col>
                                </b-row>
                                <b-row>
                                    <b-col lg="4">
                                        <b-form-group label="City" label-for="contact_address_city" label-class="required" key="address_city">
                                            <b-form-input v-model="form.contact_address_city" type="text" />
                                            <input-help :form="form" field="contact_address_city"></input-help>
                                        </b-form-group>
                                    </b-col>
                                    <b-col lg="4">
                                        <b-form-group label="State" label-for="contact_address_state" label-class="required" key="address_state">
                                            <b-form-input v-model="form.contact_address_state" type="text" />
                                            <input-help :form="form" field="contact_address_state"></input-help>
                                        </b-form-group>
                                    </b-col>
                                    <b-col lg="4">
                                        <b-form-group label="Zip" label-for="contact_address_zip" label-class="required" key="address_zip">
                                            <b-form-input v-model="form.contact_address_zip" type="text" />
                                            <input-help :form="form" field="contact_address_zip"></input-help>
                                        </b-form-group>
                                    </b-col>
                                </b-row>
                            </b-form-group>
                            <b-form-group v-if=" form.is_company " label="Referral Owner" label-for="referral_owner" :key=" 'one' ">

                                <b-form-input v-model="form.source_owner" type="text" />
                                <input-help :form="form" field="referral_owner"></input-help>
                            </b-form-group>
                            <b-form-group v-if=" form.is_company " label="Referral Source Type" label-for="referral_source_type" :key=" 'two' ">

                                <b-form-input v-model="form.source_type" type="text" />
                                <input-help :form="form" field="referral_source_type"></input-help>
                            </b-form-group>
                            <b-form-group v-if=" form.is_company " label="Web Address" label-for="web_address" :key=" 'three' ">

                                <b-form-input v-model="form.web_address" type="text" />
                                <input-help :form="form" field="web_address"></input-help>
                            </b-form-group>
                            <b-form-group v-if=" form.is_company " label="Work Phone" label-for="work_phone" :key=" 'four' ">

                                <mask-input v-model="form.work_phone" type="phone" />
                                <input-help :form="form" field="work_phone"></input-help>
                            </b-form-group>
                        </transition-group>
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

                    is_company: defaults.is_company,

                    organization: defaults.organization,
                    contact_name: defaults.id ? null : defaults.contact_name,
                    contact_address_street: defaults.contact_address_street,
                    contact_address_street2: defaults.contact_address_street2,
                    contact_address_city: defaults.contact_address_city,
                    contact_address_state: defaults.contact_address_state,
                    contact_address_zip: defaults.contact_address_zip,
                    phone: defaults.id ? null : defaults.phone,
                    type: this.sourceType,

                    source_owner: defaults.source_owner,
                    source_type : defaults.source_type,
                    web_address : defaults.web_address,
                    work_phone: defaults.work_phone,

                    id: defaults.source_id || null
                });
            },

            submitForm() {
                let url = '/business/referral-sources' + ( this.form.id ? '/' + this.form.id : '' );
                let method = this.form.id ? 'patch' : 'post';
                this.form.submit( method, url )
                    .then(response => {

                        this.$emit('saved', response.data.data);
                        this.showModal=false;
                    })
                    .catch(() => {
                    })
            },
        },

        watch: {
            value(val) {
                this.form = this.makeForm(this.source);
                this.showModal = val;
            },
            showModal(val) {
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