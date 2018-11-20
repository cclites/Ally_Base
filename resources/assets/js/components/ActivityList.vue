<template>
    <b-card>
        <b-row>
            <b-col lg="6">
                <b-btn variant="info" @click="createActivity()">Add Activity</b-btn>
            </b-col>
            <b-col lg="6" class="text-right">
                <b-form-input v-model="filter" placeholder="Type to Search" />
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
                     @filtered="onFiltered"
            >
                <template slot="actions" scope="row">
                    <b-btn size="sm" @click.stop="editActivity(row.item)" v-if="row.item.business_id || row.item.new">Edit</b-btn>
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

        <b-modal id="editActivity" :title="modalTitle" v-model="activityModal">
            <b-container fluid>
                <b-row>
                    <b-form-group label="Activity Code" label-for="code">
                        <b-form-input
                            id="code"
                            name="code"
                            type="text"
                            v-model="form.code"
                            >
                        </b-form-input>
                        <input-help :form="form" field="code" text="Enter a numerical code for this activity."></input-help>
                    </b-form-group>
                    <b-form-group label="Activity Name" label-for="name">
                        <b-form-input
                            id="name"
                            name="name"
                            type="text"
                            v-model="form.name"
                            >
                        </b-form-input>
                        <input-help :form="form" field="name" text="Enter the display name for this activity."></input-help>
                    </b-form-group>
                    <b-form-group label="Business Location">
                        <business-location-select v-model="form.business_id"></business-location-select>
                    </b-form-group>
               </b-row>
            </b-container>
            <div slot="modal-footer">
               <b-btn variant="default" @click="activityModal=false">Close</b-btn>
               <b-btn variant="danger" @click="deleteActivity()" v-if="selectedItem.id">Delete</b-btn>
               <b-btn variant="info" @click="saveActivity()">Save</b-btn>
            </div>
        </b-modal>
    </b-card>
</template>

<script>
    import FormatsListData from "../mixins/FormatsListData";
    import BusinessLocationSelect from "./business/BusinessLocationSelect";

    export default {
        components: {BusinessLocationSelect},
        mixins: [FormatsListData],

        props: {
            'activities': {},
        },

        data() {
            return {
                totalRows: 0,
                perPage: 15,
                currentPage: 1,
                sortBy: 'code',
                sortDesc: false,
                filter: null,
                activityModal: false,
                selectedItem: {},
                form: new Form(),
                fields: [
                    {
                        key: 'code',
                        label: 'Code',
                        sortable: true,
                    },
                    {
                        key: 'name',
                        label: 'Activity Name',
                        sortable: true,
                    },
                    {
                        key: 'business_id',
                        label: 'Business Location',
                        sortable: true,
                        formatter: this.showBusinessName,
                    },
                    {
                        key: 'actions',
                        class: 'hidden-print'
                    }
                ],
                items: this.activities,
            }
        },

        mounted() {
            this.totalRows = this.items.length;
        },

        computed: {
            modalTitle() {
                if (this.selectedItem.id) {
                    return 'Edit Activity';
                }
                return 'Create Activity';
            }
        },

        methods: {
            editActivity(item) {
                this.selectedItem = item;
                this.activityModal = true;
                this.form = new Form({
                    code: this.selectedItem.code,
                    name: this.selectedItem.name,
                    business_id: this.selectedItem.business_id,
                });
            },
            createActivity() {
                this.selectedItem = {};
                this.activityModal = true;
                this.form = new Form({
                    code: null,
                    name: null,
                    business_id: "",
                });
            },
            saveActivity() {
                if (this.selectedItem.id) {
                    this.form.patch('/business/activities/' + this.selectedItem.id)
                        .then(response => {
                            this.activityModal = false;
                            this.items = this.items.map(activity => {
                                if (activity.id == this.selectedItem.id) {
                                    return {
                                        ...activity,
                                        ...this.form,
                                    }
                                }
                                return activity;
                            })
                        });
                }
                else {
                    component.form.post('/business/activities')
                        .then(function(response) {
                            component.activityModal = false;
                            component.items.unshift({
                                id: response.data.data.id,
                                code: component.form.code,
                                name: component.form.name,
                                new: true,
                            })
                        });
                }
            },
            deleteActivity() {
                if (confirm('Are you sure you wish to delete this activity?')) {
                    let component = this;
                    let form = new Form();
                    form.submit('delete', '/business/activities/' + component.selectedItem.id)
                        .then(function(response) {
                            component.items = component.items.filter(activity => activity.id != component.selectedItem.id);
                            component.activityModal = false;
                        });
                }
            },
            resetModal() {
                this.modalDetails.data = '';
                this.modalDetails.index = '';
            },
            onFiltered(filteredItems) {
                // Trigger pagination to update the number of buttons/pages due to filtering
                this.totalRows = filteredItems.length;
                this.currentPage = 1;
            }
        }
    }
</script>