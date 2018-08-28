<template>
    <form @submit.prevent="submit()" @keydown="form.clearError($event.target.name)">
        <b-row>
            <b-col lg="6">
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
                <b-form-group label="Caregiver" label-for="caregiver_id">
                    <b-form-select
                            id="caregiver_id"
                            name="caregiver_id"
                            v-model="form.caregiver_id"
                    >
                        <option value="">--Select--</option>
                        <option :value="caregiver.id" v-for="caregiver in business.caregivers" :key="caregiver.id">{{ caregiver.name }}</option>
                    </b-form-select>
                    <input-help :form="form" field="caregiver_id" text="Select a caregiver."></input-help>
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
                <b-form-group label="Notes" labe-for="body">
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
    </form>
</template>

<script>
    export default {
        props: ['business', 'note'],

        data() {
            return {
                form: new Form({}),
                busy: false,
            }
        },

        computed: {
            clients() {
                return this.business.clients.sort(function(a, b) {
                    return a.name > b.name ? 1 : -1;
                });
            },
            caregivers() {
                return this.business.caregivers.sort(function(a, b) {
                    return a.name > b.name ? 1 : -1;
                });
            },
        },

        methods: {
            submit() {
                let path = '/notes';
                let method = 'post';

                if (this.note.id) {
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
                    business_id: this.business.id,
                    caregiver_id: data.caregiver_id,
                    client_id: data.client_id,
                    body: data.body,
                    tags: data.tags,
                    modal: 1, // added so controller doesn't send redirect response
                });
            },
        },

        watch: {
            note(newVal, oldVal) {
                console.log('note changed');
                this.fillForm(newVal);
            },
        },

        mounted() {
            this.fillForm({});
        },
    }
</script>
