<template>
    <b-card header="New Note" header-bg-variant="info" header-text-variant="white">
        <form @submit.prevent="saveNote()" @keydown="form.clearError($event.target.name)">
            <b-row>
                <b-col lg="6">
                    <b-form-group label="Caregiver" label-for="caregiver_id">
                        <b-form-select
                                id="caregiver_id"
                                name="caregiver_id"
                                v-model="form.caregiver_id"
                        >
                            <option value="">--Select--</option>
                            <option :value="caregiver.id" v-for="caregiver in caregivers" :key="caregiver.id">{{ caregiver.name }}</option>
                        </b-form-select>
                        <input-help :form="form" field="caregiver_id" text="Select a caregiver."></input-help>
                    </b-form-group>
                    <b-form-group label="Client" label-for="client">
                        <b-form-select
                                id="client_id"
                                name="client_id"
                                v-model="form.client_id"
                        >
                            <option value="">--Select--</option>
                            <option :value="client.id" v-for="client in clients" :key="client.id">{{ client.name }}</option>
                        </b-form-select>
                        <input-help :form="form" field="client_id" text="Select a client."></input-help>
                    </b-form-group>
                    <b-form-group label="Tags" label-for="tags">
                        <b-form-input
                                id="tags"
                                name="tags"
                                type="text"
                                v-model="form.tags"
                                maxlength="32"
                        >
                        </b-form-input>
                        <input-help :form="form" field="tags" text="Tag the note for searching."></input-help>
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Body" labe-for="body">
                        <b-form-textarea
                                id="body"
                                name="body"
                                :rows="13"
                                v-model="form.body"
                        >
                        </b-form-textarea>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="12">
                    <submit-button :submitting="submitting" variant="success" type="submit">Create</submit-button>
                </b-col>
            </b-row>
        </form>
    </b-card>
</template>

<script>
    export default {
        props: ['business'],

        data() {
            return {
                clients: this.business.clients || [],
                caregivers: this.business.caregivers || [],
                form: new Form({
                    business_id: this.business.id,
                    caregiver_id: '',
                    client_id: '',
                    body: null,
                    tags: null
                }),
                submitting: false,
            }
        },

        mounted() {
            if (!this.clients.length) this.loadClients();
            if (!this.caregivers.length) this.loadCaregivers();
        },

        methods: {
            async loadClients() {
                const response = await axios.get('/business/clients?json=1');
                this.clients = response.data;
            },

            async loadCaregivers() {
                const response = await axios.get('/business/caregivers?json=1');
                this.caregivers = response.data;
            },

            async saveNote() {
                this.submitting = true;
                try {
                    await this.form.post('/notes');
                }
                catch (err) {}
                this.submitting = false;
            }
        }

    }
</script>
