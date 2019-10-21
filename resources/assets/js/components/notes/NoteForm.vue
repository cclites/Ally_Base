<template>
    <form @submit.prevent="submit()" @keydown="form.clearError($event.target.name)">
        <b-row>
            <b-col lg="4">
                <b-form-group label="Type">
                    <b-form-radio-group v-model="form.type" name="noteType" button-variant="success">
                        <b-form-radio value="other">
                            <span class="type-icon type-icon-first">
                                <i class="fa fa-sticky-note" style="color: khaki"></i> General Note
                            </span>
                        </b-form-radio>
                        <b-form-radio value="phone">
                            <span class="type-icon type-icon-last">
                                <i class="fa fa-phone" style="color: green"></i> Phone Call
                            </span>
                        </b-form-radio>
                    </b-form-radio-group>
                    <b-form-select v-model="form.call_direction" v-if="form.type === 'phone'">
                        <option value="inbound">Inbound Phone Call</option>
                        <option value="outbound">Outbound Phone Call</option>
                    </b-form-select>
                </b-form-group>
                <business-location-form-group v-model="form.business_id"
                                              :form="form"
                                              field="business_id"
                                              help-text="">
                </business-location-form-group>
                <label>Tags - This will add the note to the entity's profile</label>
                <b-form-group label-for="client">
                    <b-form-select
                            id="client_id"
                            name="client_id"
                            v-model="form.client_id"
                    >
                        <option value="">--Client--</option>
                        <option :value="client.id" v-for="client in clients" :key="client.id">{{ client.nameLastFirst }}</option>
                    </b-form-select>
                    <input-help :form="form" field="client_id" text="Select a client."></input-help>
                </b-form-group>
                <b-form-group label-for="caregiver_id">
                    <b-form-select
                            id="caregiver_id"
                            name="caregiver_id"
                            v-model="form.caregiver_id"
                    >
                        <option value="">--Caregiver--</option>
                        <option :value="caregiver.id" v-for="caregiver in caregivers" :key="caregiver.id">{{ caregiver.nameLastFirst }}</option>
                    </b-form-select>
                    <input-help :form="form" field="caregiver_id" text="Select a caregiver."></input-help>
                </b-form-group>
                <b-form-group label-for="prospect_id">
                    <b-form-select
                            id="prospect_id"
                            name="prospect_id"
                            v-model="form.prospect_id"
                    >
                        <option value="">--Prospect--</option>
                        <option :value="prospect.id" v-for="prospect in prospects" :key="prospect.id">{{ prospect.nameLastFirst }}</option>
                    </b-form-select>
                    <input-help :form="form" field="prospect_id" text="Select a Prospect."></input-help>
                </b-form-group>
                <b-form-group label-for="referral_source_id">
                    <b-form-select
                            id="referral_source_id"
                            name="referral_source_id"
                            v-model="form.referral_source_id"
                    >
                        <option value="">--Referral Source--</option>
                        <option :value="rs.id" v-for="rs in referral_sources" :key="rs.id">{{ rs.organization }}</option>
                    </b-form-select>
                    <input-help :form="form" field="referral_source_id" text="Select a Referral Source."></input-help>
                </b-form-group>
                <!--<b-form-group label="Tags" label-for="tags">
                    <b-form-input
                            id="tags"
                            name="tags"
                            type="text"
                            v-model="form.tags"
                            maxlength="32"
                    >
                    </b-form-input>
                    <input-help :form="form" field="tags" text="Tag the note for searching."></input-help>
                </b-form-group>-->
            </b-col>
            <b-col lg="8">
                <b-form-group label="Template" label-for="note_template_id">
                    <b-form-select
                            id="note_template_id"
                            name="note_template_id"
                            v-model="noteTemplate"
                            @change="onChangeTemplate()"
                    >
                        <option value="">--Select--</option>
                        <option :value="template" v-for="template in templates" :key="template.id">{{ template.short_name }}</option>
                    </b-form-select>
                    <input-help :form="form" field="note_template_id" text="Select a note template."></input-help>
                </b-form-group>
                <b-form-group label="Title">
                    <b-form-input type="text" v-model="form.title"></b-form-input>
                </b-form-group>
                <b-form-group label="Note">
                    <b-form-textarea
                            name="body"
                            :rows="11"
                            v-model="form.body"
                    >
                    </b-form-textarea>
                </b-form-group>
            </b-col>
        </b-row>
    </form>
</template>

<script>
    import BusinessLocationFormGroup from "../business/BusinessLocationFormGroup";

    export default {
        components: {BusinessLocationFormGroup},

        props: {
            client: {
                type: Object,
                default: () => ({}),
            },
            caregiver: {
                type: Object,
                default: () => ({}),
            },
            prospect: {
                type: Object,
                default: () => ({}),
            },
            source: {
                type: Object,
                default: () => ({}),
            },
            note: {
                type: Object,
                default: () => ({}),
            },
            modal: {
                type: Number,
                default: 0,
            }
        },

        data() {
            return {
                clients: [],
                caregivers: [],
                prospects: [],
                referral_sources: [],
                templates: [],
                types: [
                    { text: 'Phone', value: 'phone' },
                    { text: 'Other', value: 'other' },
                ],
                noteTemplate: "",
                form: new Form({}),
                busy: false,
            }
        },

        mounted() {
            this.loadClients();
            this.loadCaregivers();
            this.loadProspects();
            this.loadReferralSources();
            this.loadTemplates();
            this.fillForm({});
            console.log('NoteForm mounted');
        },

        methods: {
            async loadClients() {
                console.log('loadClients called');
                const response = await axios.get('/business/clients?json=1');
                this.clients = response.data;
            },

            async loadCaregivers() {
                console.log('loadCaregivers called');
                const response = await axios.get('/business/caregivers?json=1');
                this.caregivers = response.data;
            },

            async loadProspects() {
                console.log('loadProspects called');
                const response = await axios.get('/business/prospects?json=1');
                this.prospects = response.data;
            },

            async loadReferralSources() {
                console.log('loadReferralSources called');
                const response = await axios.get(`/business/referral-sources?json=1&business_id=${this.client.business_id}`);
                this.referral_sources = response.data;
            },

            async loadTemplates() {
                console.log('loadTemplates called');
                const response = await axios.get('/note-templates?json=1');
                this.templates = response.data;
            },

            submit() {
                let path = '/notes';
                let method = 'post';

                if (this.note && this.note.id) {
                    path = '/notes/' + this.note.id;
                    method = 'patch';
                }

                this.busy = true;
                return new Promise((resolve, reject) => {
                    this.form.submit(method, path)
                        .then( ({ data }) => {
                            this.busy = false;
                            resolve(data.data);
                        })
                        .catch(e => {
                            this.busy = false;
                            reject(e);
                        });
                });
            },

            fillForm(data) {
                this.form = new Form({
                    business_id: data.business_id || this.client.business_id || "",
                    caregiver_id: data.caregiver_id || this.caregiver.id || "",
                    client_id: data.client_id || this.client.id || "",
                    prospect_id: data.prospect_id || this.prospect.id || "",
                    referral_source_id: data.referral_source_id || this.source.id || "",
                    title: data.title || "",
                    body: data.body || "",
                    tags: data.tags || "",
                    type: data.type || "other",
                    call_direction: data.call_direction || "inbound",
                    modal: this.modal, // added so controller doesn't send redirect response
                });
            },

            onChangeTemplate() {
                setTimeout(() => {
                    this.form.title = this.noteTemplate.short_name;
                    this.form.body = this.noteTemplate.note;
                });
            }
        },

        watch: {
            note(newVal, oldVal) {
                console.log('note changed');
                this.fillForm(newVal);
            },
        },
    }
</script>

<style lang="scss">
    .type-icons {
        #note-type {
            display: flex;
        }

        .custom-control {
            padding: 0;
            margin: 0;
        }

        .custom-control-indicator {
            display: none;
        }

        .type-icon {
            border: 2px solid #4d575d;
            border-radius: 4px;
            padding: 5px 20px;
            display: inline-block;

            &-first {
                border-radius: 4px 0 0 4px;
            }

            &-last {
                border-radius: 0 4px 4px 0;
            }
        }

        .custom-control-input:checked ~ .custom-control-description {
            .type-icon {
                background: #4d575d;
                
                i {
                    color: white;
                }
            }
        }
    }
</style>