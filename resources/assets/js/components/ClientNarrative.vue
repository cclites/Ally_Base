<template>
    <div>
        <div v-if="mode == 'caregiver'" class="d-flex mb-2">
            <h3 class="f-1">Caregiver's Narrative:</h3>
            <b-button class="ml-auto" variant="info" @click="showFormModal()" :disabled="authInactive">Add to Narrative</b-button>
        </div>
        <div v-else class="mb-2">
            <b-button class="ml-auto" variant="info" @click="showFormModal()" :disabled="authInactive">Add to Narrative</b-button>
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
                        <div v-if="mode == 'admin' || item.is_owner" class="ml-auto">
                            <b-button variant="info" size="sm" @click.prevent="showFormModal(item)"><i class="fa fa-edit"></i></b-button>
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
            v-model="formModal"
            :title="modalTitle"
            @ok="save()"
            ok-title="Save"
            ok-variant="info"
            size="lg"
            :busy="busy"
            @shown="focusTextarea()"
        >
            <b-container fluid>
                <b-form-textarea :rows="5" v-model="form.notes" ref="form_notes" :readonly="authInactive"></b-form-textarea>
            </b-container>
        </b-modal>

        <b-modal title="Are you sure?" v-model="confirmDeleteModal" ref="confirmDeleteModal">
            Are you sure you want to delete these narrative notes?
            <div slot="modal-footer">
               <b-btn variant="default" @click="confirmDeleteModal = false">Cancel</b-btn>
               <b-btn variant="danger" @click.prevent="confirmDeleteModal = false; destroy(deleteId, true)">Delete</b-btn>
            </div>
        </b-modal>
    </div>
</template>

<script>
import FormatsDates from "../../mixins/FormatsDates";
import FormatsStrings from "../../mixins/FormatsStrings";
import AuthUser from "../../mixins/AuthUser";

export default {
    name: 'ClientNarrative',

    mixins: [ FormatsDates, FormatsStrings, AuthUser ],

    props: {
        client: {
            required: true,
            type: Object,
            default() {
                return {};
            },
        },
        mode: { type: String, default: 'caregiver' },
    },

    data() {
        return {
            items: [],
            loading: true,
            busy: false,
            perPage: 15,
            totalRows: 0,
            currentPage: 1,
            formModal: false,
            form: new Form({ notes: '' }),
            confirmDeleteModal: false,
            deleteId: null,
            currentNote: null,
        }
    },

    computed: {
        modalTitle() {
            if (this.currentNote) {
                return 'Edit Narrative Notes';
            }
            return 'Add Narrative Notes';
        },

        url() {
            let prefix = this.mode == 'admin' ? 'business' : this.mode;
            return `/${prefix}/clients/${this.client.id}/narrative`;
        },
    },

    methods: {
        fetch() {
            this.loading = true;
            axios.get(this.url + `?json=1&per_page=${this.perPage}&page=${this.currentPage}`)
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
        
        showFormModal(note = null) {
            if (note) {
                this.currentNote = note.id;
                this.form.notes = note.notes;
            } else {
                this.currentNote = null;
                this.form.notes = '';
            }
            this.formModal = true;
        },

        focusTextarea() {
            this.$nextTick(() => {
                this.$refs.form_notes.focus();
            });
        },

        save() {
            this.busy = true;
            let method = this.currentNote ? 'patch' : 'post';
            this.form.submit(method, this.url + (this.currentNote ? `/${this.currentNote}` : ''))
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
            form.submit('delete', this.url + `/${id}`)
                .then( ({ data }) => {
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