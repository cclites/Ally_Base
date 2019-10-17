<template>
    <form @submit.prevent="submit()" @keydown="form.clearError($event.target.name)">
        <b-modal :title="title"
            v-model="showModal"
            @cancel="onCancel"
            :no-close-on-esc="busy"
            :no-close-on-backdrop="busy"
        >
            <b-row>
                <b-col>
                    <div class="d-flex ">
                        <b-form-checkbox class="f-1" v-model="form.is_emergency" :disabled="busy">
                            Emergency Contact
                        </b-form-checkbox>
                        <b-form-checkbox class="f-1" v-model="form.has_poa" :disabled="busy">
                            Has Power of Attorney
                        </b-form-checkbox>
                    </div>
                    <div class="d-flex ">
                        <b-form-checkbox class="f-1" v-model="form.is_payer" :disabled="busy">
                            Is Payer
                        </b-form-checkbox>
                        <b-form-checkbox class="f-1" v-model="form.has_login_access" :disabled="busy">
                            Has Login Access
                        </b-form-checkbox>
                    </div>
                    <b-form-group label="Contact Name" label-for="name" label-class="required">
                        <b-form-input v-model="form.name" type="text" required :disabled="busy" />
                        <input-help :form="form" field="name"></input-help>
                    </b-form-group>
                    <b-form-group label="Relationship" label-for="relationship" label-class="required">
                        <b-select v-model="form.relationship" name="relationship" id="relationship" :disabled="busy">
                            <option value="family">Family</option>
                            <option value="physician">Physician</option>
                            <option value="medical professional">Medical Professional</option>
                            <option value="other">Other</option>
                            <option value="custom">Custom</option>
                        </b-select>
                        <b-form-input v-if=" [ 'custom', 'family', 'medical professional' ].includes( form.relationship )" v-model="form.relationship_custom" type="text" required class="mt-2" :disabled="busy" placeholder="Enter Specific Relationship Type"/>
                        <input-help :form="form" field="relationship"></input-help>
                    </b-form-group>
                    <b-form-group label="Email" label-for="email">
                        <b-form-input v-model="form.email" type="email" max="255" :disabled="busy" />
                        <input-help :form="form" field="email"></input-help>
                    </b-form-group>
                    <b-row>
                        <b-col sm="6">
                            <b-form-group label="Mobile Phone" label-for="phone1">
                                <b-form-input v-model="form.phone1" type="text" max="45" :disabled="busy" />
                                <input-help :form="form" field="phone1"></input-help>
                            </b-form-group>
                        </b-col>
                        <b-col sm="6">
                            <b-form-group label="Home Phone" label-for="phone2">
                                <b-form-input v-model="form.phone2" type="text" max="45" :disabled="busy" />
                                <input-help :form="form" field="phone2"></input-help>
                            </b-form-group>
                        </b-col>
                    </b-row>
                    <b-row>
                        <b-col sm="6">
                            <b-form-group label="Work Phone" label-for="work_phone">
                                <b-form-input v-model="form.work_phone" type="text" max="45" :disabled="busy" />
                                <input-help :form="form" field="work_phone"></input-help>
                            </b-form-group>
                        </b-col>
                        <b-col sm="6">
                            <b-form-group label="Fax Number" label-for="fax_number">
                                <b-form-input v-model="form.fax_number" type="text" max="45" :disabled="busy" />
                                <input-help :form="form" field="fax_number"></input-help>
                            </b-form-group>
                        </b-col>
                    </b-row>
                    <b-form-group label="Street Address" label-for="address">
                        <b-form-input v-model="form.address" type="text" max="255" :disabled="busy" />
                        <input-help :form="form" field="address"></input-help>
                    </b-form-group>
                    <b-form-group label="City" label-for="city">
                        <b-form-input v-model="form.city" type="text" max="45" :disabled="busy" />
                        <input-help :form="form" field="city"></input-help>
                    </b-form-group>
                    <b-row>
                        <b-col sm="6">
                            <b-form-group label="State" label-for="state">
                                <b-form-input v-model="form.state" type="text" max="45" :disabled="busy" />
                                <input-help :form="form" field="state"></input-help>
                            </b-form-group>
                        </b-col>
                        <b-col sm="6">
                            <b-form-group label="Zipcode" label-for="zip">
                                <b-form-input v-model="form.zip" type="text" max="45" :disabled="busy" />
                                <input-help :form="form" field="zip"></input-help>
                            </b-form-group>
                        </b-col>
                    </b-row>
                </b-col>
            </b-row>
            <div slot="modal-footer">
                <b-button variant="success"
                    type="submit"
                    :disabled="busy"
                >
                    {{ buttonText }}
                </b-button>
                <b-btn variant="default" @click="showModal = false" :disabled="busy">Cancel</b-btn>
            </div>
        </b-modal>
    </form>
</template>

<script>
    import AuthUser from '../mixins/AuthUser';
    export default {
        mixins: [ AuthUser ],

        props: {
            value: Boolean,
            source: Object,
            client: Object,
        },

        data() {
            return {
                busy: false,
                form: this.makeForm(this.source),
                showModal: this.value,
            }
        },

        computed: {
            title() {
                return (this.source.id) ? 'Edit Contact' : 'Add New Contact';
            },
            buttonText() {
                return (this.source.id) ? 'Save' : 'Create';
            },
            isClient() {
                return this.authUser.id === this.client.id;
            },
        },

        methods: {
            makeForm(defaults = {}) {
                return new Form({
                    name: defaults.name,
                    relationship: defaults.relationship ? defaults.relationship : 'other',
                    relationship_custom: defaults.relationship_custom,
                    email: defaults.email,
                    phone1: defaults.phone1,
                    phone2: defaults.phone2,
                    work_phone: defaults.work_phone,
                    fax_number: defaults.fax_number,
                    address: defaults.address,
                    city: defaults.city,
                    state: defaults.state,
                    zip: defaults.zip,
                    is_payer: defaults.is_payer ? true : false,
                    is_emergency: defaults.is_emergency ? true : false,
                    has_poa: defaults.has_poa ? true : false,
                    has_login_access: defaults.has_login_access ? true : false,
                });
            },

            submit() {
                this.busy = true;
                let method = this.source.id ? 'patch' : 'post';
                let url = this.source.id ? `/business/clients/${this.client.id}/contacts/${this.source.id}` : `/business/clients/${this.client.id}/contacts`;

                if (this.isClient) {
                    url = this.source.id ? `/contacts/${this.source.id}` : '/contacts';
                }
                this.form.submit(method, url)
                    .then( ({ data }) => {
                        this.$emit(this.source.id ? 'updated' : 'created', data.data);
                        this.showModal = false;
                    })
                    .catch(e => {
                    })
                    .finally(() => this.busy = false)
            },

            onCancel() {
                this.value = {};
            },
        },

        watch: {
            value(val) {
                if (! val) {
                    // clear the form on close so the data updates if the
                    // edit modal is opened again for the same object.
                    this.form = this.makeForm({});
                } else {
                    this.form = this.makeForm(this.source);
                }
                this.showModal = val;
            },
            
            showModal(val) {
                this.$emit('input', val);
            }
        }
    }
</script>