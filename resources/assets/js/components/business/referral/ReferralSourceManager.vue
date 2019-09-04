<template>
    <b-card>
        <b-row class="mb-2">
            <b-col lg="3">
                <b-btn variant="info" @click="showModal=true">Add Referral Source</b-btn>
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
                    <b-btn size="sm" :href="'/business/referral-sources/' + row.item.id">
                        <i class="fa fa-edit"></i>
                    </b-btn>
                    <b-btn size="sm" @click="destroy(row.item)" variant="danger">
                        <i class="fa fa-trash"></i>
                    </b-btn>
                </template>
            </b-table>
        </div>

        <b-row>
            <b-col lg="6" >
                <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" />
            </b-col>
            <b-col lg="6" class="text-right">
                Showing {{ perPage < totalRows ? perPage : totalRows }} of {{ totalRows }} results
            </b-col>
        </b-row>

        <business-referral-source-modal
            v-model="showModal"
            :source="editSource" 
            @saved="updateList"
            :source-type="sourceType"
        ></business-referral-source-modal>
    </b-card>
</template>

<script>
    import FormatsDates from "../../../mixins/FormatsDates";
    import FormatsListData from "../../../mixins/FormatsListData";

    export default {
        mixins: [FormatsDates, FormatsListData],

        props: ['referralSources', 'editSourceId', 'createSource', 'sourceType'],

        data() {
            return {
                items: this.referralSources || [],
                showModal: !!this.editSourceId || !!this.createSource,
                editSource: this.find(this.editSourceId, this.referralSources) || {},
                active: 'active',
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
                    {
                        key: 'phone',
                        label: 'Phone',
                        sortable: true
                    },
                    'actions'
                ]
            }
        },

        mounted() {
            this.totalRows = this.items.length;
        },

        methods: {
            edit(id) {
                this.editSource = this.find(id);
                this.showModal = true;
            },
            create() {
                this.editSource = {};
                this.showModal = true;
            },
            find(id, list=null) {
                if (!list) list = this.items;
                return list.find(item => item.id == id);
            },
            updateList(source) {
                let index = this.items.findIndex(item => item.id == source.id);
                if (index === -1) {
                    this.items.push(source);
                }
                else {
                    Vue.set(this.items, index, source);
                }
            },
            destroy(item) {
                if (! confirm('Are you sure you want to delete this referral source?')) {
                    return;
                }
                
                let form = new Form({});
                form.submit('DELETE', `/business/referral-sources/${item.id}`)
                    .then(response => {
                        let index = this.items.findIndex(x => x.id == item.id);
                        if (index >= 0) {
                            this.items.splice(index, 1);
                        }
                    })
                    .catch(e => {
                    })
            },
            formatContacts(){

            },
        }
    }
</script>

<style scoped>

</style>