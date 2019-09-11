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
                         ref="table"
                >
                    <template slot="contact_name" scope="row">
                        <b-form-input :value="row.item.contact_name" v-model="row.item.contact_name"></b-form-input>
                    </template>

                    <template slot="phone" scope="row">
                        <b-form-input :value="row.item.phone" v-model="row.item.phone"></b-form-input>
                    </template>

                    <template slot="actions" scope="row">
                        <b-btn size="sm" @click="update(row.item)" class="mt-1">
                            <i class="fa fa-save"></i>
                        </b-btn>
                        <b-btn v-if="row.item.active" size="sm" @click="deactivate(row.item, 0)" variant="danger"  class="mt-1">
                            <i class="fa fa-trash"></i>
                        </b-btn>
                        <b-btn v-else size="sm" @click="deactivate(row.item, 1)" variant="success" class="mt-1">
                            <i class="fa fa-plus-square"></i>
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
            update(resourceData){
                let form = new Form({
                    contact_name: resourceData.contact_name,
                    phone: resourceData.phone,
                    id: resourceData.id,
                    active: resourceData.active,
                    organization: this.source.organization,
                    chain_id: this.source.id,
                    type: this.sourceType,
                    deactivate: resourceData.deactivate ? resourceData.deactivate : null,
                });

                this.loading = true;
                let method = resourceData.id ? 'patch' : 'post';
                let url = resourceData.id ? `/business/referral-sources/${resourceData.id}` : '/business/referral-sources';
                form.submit(method, url)
                    .then(response => {

                        if(method === 'post'){
                            resourceData.id = response.data.data.id;

                            let data = {response: response.data.data, item_id: this.source.id};
                            this.$emit('saved', data);
                        }else{
                            let index = this.items.findIndex(x => x.id == response.data.data.id);
                            let originalItems = this.items;

                            if(index >= 0){
                                originalItems[index] = response.data.data;
                            }

                            this.items = originalItems;

                            let data = {response: response.data.data, item_id: this.source.id};
                            this.$emit('saved', data);
                        }

                    })
                    .finally(() => this.loading = false)
            },

            deactivate(resourceData, active){

                if (!active && ! confirm('Are you sure you want to deactivate this referral source?')) {
                    return;
                }

                let form = new Form({
                    contact_name: resourceData.contact_name,
                    phone: resourceData.phone,
                    id: resourceData.id,
                    active: active,
                });

                form.submit('DELETE', `/business/referral-sources/${resourceData.id}`)
                    .then(response => {
                        resourceData.active = active;
                        let data = {response: this.items, id: this.source.id}
                        this.$emit('deactivated', data);
                    })
                    .catch(e => {
                    })

            },

            add(){
               this.items.push({
                    contact_name: '',
                    phone: '',
                    id: '',
                    active: true,
                });
            }
        },

        watch: {
            value(val) {
                console.log("watching val");
                this.items = this.source.contacts;
                this.showModal = val;
            },

            showModal(val) {
                this.$emit('input', val);
            },

        }
    }
</script>

<style scoped>
    .modal-dialog {
        max-width: 900px !important;
    }
</style>