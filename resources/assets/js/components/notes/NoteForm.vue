<template>
    <form @submit.prevent="submit()" @keydown="form.clearError($event.target.name)">
        <b-row>
            <b-col lg="6">
                <b-form-group label="Type">
                    <b-form-radio-group id="note-type" v-model="form.type" name="noteType">
                        <b-form-radio value="phone" name="typePhone">Phone</b-form-radio>
                        <b-form-radio value="other">Other</b-form-radio>
                    </b-form-radio-group>
                </b-form-group>
                <b-form-group label="Client" label-for="client">
                    <b-form-select
                            id="client_id"
                            name="client_id"
                            v-model="form.client_id"
                    >
                        <option value="">--Select--</option>
                        <option :value="client.id" v-for="client in clients" :key="client.id">{{ client.nameLastFirst }}</option>
                    </b-form-select>
                    <input-help :form="form" field="client_id" text="Select a client."></input-help>
                </b-form-group>
                <b-form-group label="Caregiver" label-for="caregiver_id">
                    <b-form-select
                            id="caregiver_id"
                            name="caregiver_id"
                            v-model="form.caregiver_id"
                    >
                        <option value="">--Select--</option>
                        <option :value="caregiver.id" v-for="caregiver in caregivers" :key="caregiver.id">{{ caregiver.nameLastFirst }}</option>
                    </b-form-select>
                    <input-help :form="form" field="caregiver_id" text="Select a caregiver."></input-help>
                </b-form-group>
                <b-form-group label="Prospect" label-for="prospect_id">
                    <b-form-select
                            id="prospect_id"
                            name="prospect_id"
                            v-model="form.prospect_id"
                    >
                        <option value="">--Select--</option>
                        <option :value="prospect.id" v-for="prospect in prospects" :key="prospect.id">{{ prospect.nameLastFirst }}</option>
                    </b-form-select>
                    <input-help :form="form" field="prospect_id" text="Select a Prospect."></input-help>
                </b-form-group>
                <b-form-group label="Referral Source" label-for="referral_source_id">
                    <b-form-select
                            id="referral_source_id"
                            name="referral_source_id"
                            v-model="form.referral_source_id"
                    >
                        <option value="">--Select--</option>
                        <option :value="rs.id" v-for="rs in referral_sources" :key="rs.id">{{ rs.organization }}</option>
                    </b-form-select>
                    <input-help :form="form" field="referral_source_id" text="Select a Referral Source."></input-help>
                </b-form-group>
                <business-location-form-group v-model="form.business_id"
                                              form="form"
                                              field="business_id"
                                              help-text="">
                </business-location-form-group>
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
            <b-col lg="6">
                <b-form-group label="Note Template" label-for="note_template_id">
                    <b-form-select
                            id="note_template_id"
                            name="note_template_id"
                            v-model="noteTemplate"
                            @change="onChangeTemplate()"
                    >
                        <option value="">--Select--</option>
                        <option :value="template.note" v-for="template in templates" :key="template.id">{{ template.short_name }}</option>
                    </b-form-select>
                    <input-help :form="form" field="note_template_id" text="Select a note template."></input-help>
                </b-form-group>
                <b-form-group label="Notes" labe-for="body">
                    <b-form-textarea
                            id="body"
                            name="body"
                            :rows="14"
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
                const response = await axios.get('/business/referral-sources?json=1');
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
                    body: data.body || "",
                    tags: data.tags || "",
                    type: data.type || "phone",
                    modal: this.modal, // added so controller doesn't send redirect response
                });
            },

            onChangeTemplate(value) {
                setTimeout(() => {
                    this.form.body = this.noteTemplate;
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
