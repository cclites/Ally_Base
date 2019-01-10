<template>
    <b-card>
        <loading-card v-show="loading"></loading-card>

        <div v-show="! loading">
            <b-btn variant="info" class="mb-3" @click="create()">Add Note Template</b-btn>

            <div class="table-responsive">
                <b-table bordered striped hover show-empty
                        :items="items"
                        :fields="fields"
                        :current-page="currentPage"
                        :per-page="perPage"
                        :sort-by.sync="sortBy"
                        @filtered="onFiltered"
                >
                    <template slot="action" scope="data">
                        <b-btn variant="secondary" @click="edit(data.item)">
                            <i class="fa fa-edit"></i>
                        </b-btn>
                    </template>
                </b-table>
            </div>

            <b-row>
                <b-col lg="6">
                    <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage"/>
                </b-col>
                <b-col lg="6" class="text-right">
                    Showing {{ perPage < totalRows ? perPage : totalRows }} of {{ totalRows }} results
                </b-col>
            </b-row>
        </div>

        <b-modal id="noteTemplateModal" :title="modalTitle" v-model="showModal" size="lg">
            <note-template-form :template="template" :modal="1" ref="templateForm" />

            <div slot="modal-footer">
               <b-btn variant="default" @click="showModal=false">Close</b-btn>
               <b-btn variant="danger" @click="destroy()" v-if="template.id">Delete</b-btn>
               <b-btn variant="info" @click="save()">Save</b-btn>
            </div>
        </b-modal>
    </b-card>
</template>

<script>
    import FormatsDates from '../../mixins/FormatsDates';
    export default {      
        mixins: [ FormatsDates ],

        props: {
            'templates': Array,
        },

        data() {
            return {
                template: {},
                showModal: false,
                items: this.templates,
                searchForm: {
                    active: true,
                    short_name: '',
                    note: ''
                },
                totalRows: 0,
                perPage: 15,
                currentPage: 1,
                sortBy: 'created_at',
                loading: false,
                fields: [
                    {
                        key: 'short_name',
                        label: 'Short Name',
                        sortable: true
                    },
                    {
                        key: 'active',
                        label: 'Active',
                        sortable: true,
                        formatter: d => d ? 'Y' : 'N',
                    },
                    {
                        key: 'note',
                        label: 'Preview',
                        sortable: false,
                        formatter: d => d.substr(0, 50)
                    },
                    'action'
                ]
            }
        },

        mounted() {
            this.totalRows = this.items.length;
        },

        computed: {
            modalTitle() {
                return this.template.id ? 'Edit Note Template' : 'Add Note Template';
            },
        },

        methods: {
            
            onFiltered(filteredItems) {
                // Trigger pagination to update the number of buttons/pages due to filtering
                this.totalRows = filteredItems.length;
                this.currentPage = 1;
            },
            
            create() {
                this.template = {
                    
                };
                this.showModal = true;
            },

            edit(template) {
                this.template = template;
                this.showModal = true;
            },
            
            save() {
                this.$refs.templateForm.submit()
                    .then(template => {
                        if (this.template.id) {
                            this.items = this.items.filter(obj => obj.id != this.template.id);
                        }
                        this.items.push(template);
                        this.template = {update: Math.random()};
                        this.showModal = false;
                    })
                    .catch(e => {
                        console.log(e);
                    })
            },

            destroy() {
                let f = new Form({});

                f.submit('delete', '/note-templates/' + this.template.id)
                    .then( ({ data }) => {
                        this.items = this.items.filter(obj => obj.id != this.template.id);
                        this.template = {};
                        this.showModal = false;
                    })
            },
        }
    }
</script>

<style>
.datepicker { z-index: 1000!important };
</style>