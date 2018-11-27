<template>
    <b-card>
        <div class="client-details mb-4">
            <h1>Client: {{ client.name }}</h1>
        </div>

        <div class="d-flex mb-2">
            <h3 class="f-1">Caregiver's Narrative:</h3>
            <b-button class="ml-auto" variant="success" @click="showAddModal()">Add to Narrative</b-button>
        </div>

        <loading-card v-if="loading" />
        <div v-else-if="totalRows == 0" class="m-4">
            <div role="alert" aria-live="polite">
                <div class="text-center my-2">There are no records to show</div>
            </div>
        </div>
        <div v-else>
            <b-card-group deck class="narrative">
                <b-card v-for="item in items" :key="item.id" class="item">
                    <div class="d-flex">
                        <div class="f-1 card-text" style="white-space: pre-wrap">{{ item.notes }}</div>
                        <div class="ml-auto">
                            <b-button variant="danger" size="sm" @click.prevent="destroy(item.id)"><i class="fa fa-times"></i></b-button>
                        </div>
                    </div>
                    <template slot="footer">
                        Added by {{ item.creator.name }} ({{ stringFormat(item.creator.role_type) }})
                        on {{ formatDateTimeFromUTC(item.created_at) }}
                    </template>
                </b-card>
            </b-card-group>

            <b-row>
                <b-col lg="6" >
                    <b-pagination :total-rows="totalRows"
                        :per-page="perPage"
                        v-model="currentPage"
                    />
                </b-col>
                <b-col lg="6" class="text-right">
                    Showing {{ perPage < totalRows ? perPage : totalRows }} of {{ totalRows }} results
                </b-col>
            </b-row>
        </div>

        <b-modal v-if="client"
            v-model="addModal"
            title="Add Narrative Notes"
            @ok="add()"
            ok-title="Add Notes"
            ok-variant="success"
            size="lg"
            :busy="busy"
            @shown="focusTextarea()"
        >
            <b-container fluid>
                <b-form-textarea :rows="5" v-model="form.notes" ref="form_notes"></b-form-textarea>
            </b-container>
        </b-modal>

        <b-modal title="Are you sure?" v-model="confirmDeleteModal" ref="confirmDeleteModal">
            Are you sure you want to delete these narrative notes?
            <div slot="modal-footer">
               <b-btn variant="default" @click="confirmDeleteModal = false">Cancel</b-btn>
               <b-btn variant="danger" @click.prevent="confirmDeleteModal = false; destroy(deleteId, true)">Delete</b-btn>
            </div>
        </b-modal>
    </b-card>
</template>

<script>
import FormatsDates from "../../mixins/FormatsDates";
import FormatsStrings from "../../mixins/FormatsStrings";

export default {
    name: 'CaregiverClientNarrative',

    mixins: [ FormatsDates, FormatsStrings ],

    props: {
        client: {
            required: true,
            type: Object,
            default() {
                return {};
            },
        },
    },

    data() {
        return {
            items: [],
            loading: true,
            busy: false,
            perPage: 15,
            totalRows: 0,
            currentPage: 1,
            addModal: false,
            form: new Form({ notes: '' }),
            confirmDeleteModal: false,
            deleteId: null,
        }
    },

    methods: {
        fetch() {
            this.loading = true;
            axios.get(`/caregiver/clients/${this.client.id}/narrative?json=1&per_page=${this.perPage}&page=${this.currentPage}`)
                .then( ({ data }) => {
                    this.items = data.data;
                    this.totalRows = data.total;
                    this.currentPage = data.current_page;
                    this.loading = false;
                })
                .catch(e => {
                    this.loading = false;
                })
        },
        
        showAddModal() {
            this.form.notes = '';
            this.addModal = true;
        },

        focusTextarea() {
            this.$nextTick(() => {
                this.$refs.form_notes.focus();
            });
        },

        add() {
            this.busy = true;
            this.form.post(`/caregiver/clients/${this.client.id}/narrative`)
                .then( ({ data }) => {
                    if (this.currentPage == 1) {
                        this.fetch();
                    } else {
                        this.currentPage = 1;
                    }
                    this.busy = false;
                })
                .catch(e => {
                    this.busy = false;
                })
        },

        destroy(id, confirmed = false) {
            if (! confirmed) {
                this.deleteId = id;
                this.confirmDeleteModal = true;
                return;
            }

            this.busy = true;
            let form = new Form();
            form.submit('delete', `/caregiver/clients/${this.client.id}/narrative/${id}`)
                .then( ({ data }) => {
                    // this.items = this.items.filter(item => item.id != id);
                    this.fetch();
                    this.busy = false;
                })
                .catch(e => {
                    this.busy = false;
                })
        },
    },

    watch: {
        currentPage() {
            this.fetch();
        }
    },

    mounted() {
        this.fetch();
    },
}
</script>

<style scoped>
.narrative { flex-flow: column; }
.narrative .item { margin-bottom: 2rem; }
</style>