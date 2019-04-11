<template>
    <b-card header="Other Restrictions"
        header-bg-variant="info"
        header-text-variant="white"
    >
        <b-btn variant="info" class="mb-3" @click="add()">Add Other Restriction</b-btn>

        <div class="table-responsive">
            <b-table bordered striped hover show-empty
                :items="items"
                :fields="fields"
                :per-page="0"
                sort-by="name"
                :busy="loading"
            >
                <template slot="actions" scope="row">
                    <b-btn size="sm" @click="edit(row.item)">Edit</b-btn>
                    <b-btn size="sm" variant="danger" @click="destroy(row.item)">Delete</b-btn>
                </template>
            </b-table>
        </div>

        <b-modal :title="`${modalVerb} Other Restriction`" v-model="showModal">
            <b-container fluid>
                <b-row>
                    <b-col>
                        <b-form-group label="Restriction Description:">
                            <b-form-textarea v-model="form.description" rows="3" max="255"></b-form-textarea>
                        </b-form-group>
                    </b-col>
                </b-row>
            </b-container>
            <div slot="modal-footer">
                <b-btn variant="default" @click="showModal=false">Close</b-btn>
                <b-btn variant="info" @click="submit()" :disabled="busy">{{ modalVerb }} Other Restriction</b-btn>
            </div>
        </b-modal>

        <confirm-modal title="Are you sure?" ref="confirmDelete" yesButton="Delete Restriction">
            <p>Are you sure you wish to delete the restriction "{{ current.description }}"?</p>
        </confirm-modal>
    </b-card>
</template>

<script>
    export default {
        props: ['caregiver'],

        data() {
            return {
                loading: false,
                busy: false,
                showModal: false,
                items: [],
                fields: [
                    { key: 'description', sortable: true },
                    { key: 'actions', label: ' ' },
                ],
                form: new Form({
                    description: this.description,
                }),
                current: {},
            };
        },

        computed: {
            modalVerb() {
                return this.current.id ? 'Update' : 'Add';
            },
        },

        methods: {
            add() {
                this.current = {};
                this.form.reset(false);
                this.showModal = true;
            },

            edit(item) {
                this.current = item;
                this.form.fill(this.current);
                this.showModal = true;
            },

            destroy(item) {
                this.current = item;
                this.$refs.confirmDelete.confirm(() => {
                    this.loading = true;
                    axios.delete(`/business/caregivers/${this.caregiver.id}/restrictions/${this.current.id}`)
                        .then( ({ data }) => {
                            this.items = data;
                        })
                        .catch(e => {})
                        .finally(() => {
                            this.loading = false;
                        });
                });
            },

            submit() {
                let url = `/business/caregivers/${this.caregiver.id}/restrictions`;
                let method = 'POST';
                if (this.current.id) {
                    url = `/business/caregivers/${this.caregiver.id}/restrictions/${this.current.id}`;
                    method = 'PATCH';
                }
                this.busy = true;
                this.form.submit(method, url)
                    .then( ({ data }) => {
                        this.items = data;
                        this.showModal = false;
                    })
                    .catch(e => {})
                    .finally(() => {
                        this.busy = false;
                    });
            },

            async fetch() {
                this.loading = true;
                axios.get(`/business/caregivers/${this.caregiver.id}/restrictions`)
                    .then( ({ data }) => {
                        this.items = data;
                    })
                    .catch(e => {})
                    .finally(() => {
                        this.loading = false;
                    });
            },
        },

        async mounted() {
            await this.fetch();
        },
    }
</script>