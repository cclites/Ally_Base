<template>
        <b-modal id="EditReferralModal" v-model="showModal" class="edit-modal" size="lg">

            <b-row>
                <b-col lg="12">
                    <b-form-group label="Organization Name" label-for="organization">
                        <b-form-input v-model="source.organization" type="text" required disabled/>
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
                    <template slot="contact_name" scope="row">
                        <b-form-input :value="row.item.contact_name" v-model="row.item.contact_name"></b-form-input>
                    </template>

                    <template slot="phone" scope="row">
                        <b-form-input :value="row.item.phone" v-model="row.item.phone"></b-form-input>
                    </template>

                    <template slot="actions" scope="row">
                        <b-btn size="sm" @click="update(row.item)">
                            <i class="fa fa-save"></i>
                        </b-btn>
                        <b-btn size="sm" @click="destroy(row.item.id)" variant="danger">
                            <i class="fa fa-trash"></i>
                        </b-btn>
                    </template>
                </b-table>
            </div>

            <div slot="modal-footer">
                <b-btn variant="info" @click="add">New Source</b-btn>
                <b-btn variant="default" @click="showModal=false">Close</b-btn>
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
                loading: false,
                showModal: this.value,
                items: [],
                item: '',
                totalRows: 0,
                currentPage: 1,
                perPage: 25,
                filter: null,
                search: null,
                sortBy: 'organization',
                sortDesc: false,
                fields: [
                    {
                        key: 'contact_name',
                        label: 'Contact Name',
                        sortable: true,
                    },
                    {
                        key: 'phone',
                        label: 'Phone',
                        sortable: true,
                    },

                    'actions'
                ]
            }
        },

        computed: {

        },

        methods: {
            update(resource){
                let form = new Form({
                    contact_name: resource.contact_name,
                    phone: resource.phone,
                    id: resource.id,
                    organization: this.source.organization,
                    chain_id: this.source.id,
                    type: this.sourceType
                });

                this.loading = true;
                let method = resource.id ? 'patch' : 'post';
                let url = resource.id ? `/business/referral-sources/${resource.id}` : '/business/referral-sources';
                form.submit(method, url)
                    .then(response => {
                        if(method === 'post'){
                            resource.id = response.data.data.id;
                            let data = {response: response.data.data, item_id: this.source.id};
                            this.$emit('saved', data);
                        }
                    })
                    .finally(() => this.loading = false)
            },

            destroy(id){

                if (! confirm('Are you sure you want to delete this referral source?')) {
                    return;
                }

                let form = new Form({});
                form.submit('DELETE', '/business/referral-sources/' + id)
                    .then(response => {
                        let index = this.items.findIndex(x => x.id == id);
                        if (index >= 0) {
                            this.items.splice(index, 1);
                        }
                        this.$emit('deleted', {item_id: this.source.id, id: id});
                    })
                    .catch(e => {
                    })
            },

            add(){
               this.items.push({
                    contact_name: '',
                    phone: '',
                    id: ''
                });
            }

        },

        watch: {
            value(val) {
                this.items = this.source.contacts;
                this.showModal = val;
            },

            showModal(val) {
                this.$emit('input', val);
            }
        }
    }
</script>

<style scoped>
    .modal-dialog {
        max-width: 900px !important;
    }
</style>