<!-- Referral Sources Left Side Menu Option-->
<template>
    <b-card>
        <b-row class="mb-2">
            <b-col lg="3">
                <b-btn variant="info" @click="showAddReferralModal=true">Add Referral Source</b-btn>
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
                <template slot="edit" scope="row">
                    <b-btn size="sm" @click="edit(row.item.id)">
                        <i class="fa fa-edit"></i>
                    </b-btn>
                    <!--b-btn size="sm" @click="destroy(row.item)" variant="danger">
                        <i class="fa fa-trash"></i>
                    </b-btn-->
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
            v-model="showAddReferralModal"
            :source="addSource"
            @saved="updateList"
            :source-type="sourceType"
        ></business-referral-source-modal>

        <business-referral-source
                v-model="showEditReferralModal"
                :source="editSource"
                @saved="updateAfterAddEdit"
                :source-type="sourceType"
                @deactivated="deactivated"
        ></business-referral-source>
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
                showAddReferralModal: false,
                showEditReferralModal: false,
                editSource: [],
                addSource: '',
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

                    'edit'
                ]
            }
        },

        mounted() {
            this.totalRows = this.items.length;
        },

        methods: {
            edit(id) {
                this.editSource = this.referralSources[id];
                this.showEditReferralModal = true;
            },
            create() {
                this.editSource = {};
                this.showAddReferralModal = true;
            },

            /*
            destroy(item) {
                if (! confirm('Are you sure you want to delete this referral source?')) {
                    return;
                }
                
                let form = new Form({});
                form.submit('DELETE', `/business/referral-sources/organization/${item.organization}`)
                    .then(response => {
                        this.items.splice(item.id, 1);
                        this.$refs.table.refresh();
                    })
                    .catch(e => {
                    })
            },
             */
            updateList(response){
                window.location.reload();
            },

            updateAfterAddEdit(data) {
                let resource = this.items[data.item_id];
                let index = resource.contacts.findIndex(x => x.id === data.response.id);

                if (index >= 0) {
                    this.items[data.item_id].contacts[index] = data.response;
                } else{
                    this.items[data.item_id].contacts.push(data.response);
                }

                this.items[data.item_id].contact_name = this.stringifyContactNames(this.items[data.item_id].contacts);

            },
            deactivated(data){
                this.items[data.id].contacts = data.response;
                this.items[data.id].contact_name = this.stringifyContactNames(this.items[data.id].contacts);
            },

            stringifyContactNames(contacts)
            {
                let contact_name = contacts.map(function(x){
                    if(x.active){
                        return x.contact_name ;
                    }
                    return '';
                });

                contact_name = contact_name.toString().replace(/,\s*$/, "");

                if (contact_name[0] == ',') {
                    contact_name = contact_name.substring(1);
                }

                return contact_name;
            }
        }
    }
</script>

<style scoped>

</style>