<template>
        <b-modal id="filterColumnsModal" v-model="showEditModal">
            <b-container fluid>
                <b-row>
                    <b-col lg="12">
                        <b-form-group label="Organization Name" label-for="organization" label-class="required">
                            <b-form-input v-model="form.organization" type="text" required />
                            <input-help :form="form" field="organization"></input-help>
                        </b-form-group>
                    </b-col>
                </b-row>
                <div class="table-responsive">
                    <b-table bordered striped hover show-empty
                             :items="items"
                             :fields="fields"
                             :current-page="currentPage"
                             :per-page="perPage"
                             :filter="filter"
                             :sort-by.sync="sortBy"
                             :sort-desc.sync="sortDesc"
                    >
                        <template slot="actions" scope="row">
                            <!--b-btn size="sm" :href="'/business/referral-sources/' + row.item.id">
                                <i class="fa fa-edit"></i>
                            </b-btn-->
                            <b-btn size="sm" @click="save(row)">
                                <i class="fa fa-edit"></i>
                            </b-btn>
                            <b-btn size="sm" @click="destroy(row.item.id)" variant="danger">
                                <i class="fa fa-trash"></i>
                            </b-btn>
                        </template>
                    </b-table>
                </div>
            </b-container>
            <div slot="modal-footer">
                <b-btn variant="default" @click="hideModal=false">Close</b-btn>
            </div>
        </b-modal>
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
                showEditModal: this.value,
                items: this.source,
                totalRows: 0,
                currentPage: 1,
                perPage: 25,
                filter: null,
                search: null,
                sortBy: 'organization',
                sortDesc: false,
                fields: [
                    {
                        key: 'organization',
                        label: 'Organization',
                        sortable: true
                    },
                    {
                        key: 'contact_name',
                        label: 'Contacts',
                        sortable: true,
                    },

                    'actions'
                ]
            }
        },

        computed: {

        },

        methods: {
            makeForm(defaults = {}) {
                return new Form({});
                /*
                return new Form({
                    organization: defaults.organization,
                    contact_name: defaults.contact_name,
                    phone: defaults.phone,
                    type: this.sourceType,
                });*/
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
                this.$emit('visible', val);
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