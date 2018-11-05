<template>
    <b-card header="Tasks" header-bg-variant="info" header-text-variant="white">
        <b-row class="mb-3">
            <b-col lg="4">
                <!-- <b-form-group class="mr-2" label="Quick Filter" for="quick-filter"> -->
                    <label for="quick-filter" class="mr-1">Quick Filter:</label>
                    <b-form-select v-model="filter" id="quick-filter">
                        <option value="">---</option>
                        <option value="created">Created By Me</option>
                        <option value="assigned">Assigned To Me</option>
                    </b-form-select>
                <!-- </b-form-group> -->
            </b-col>

            <b-col lg="4">
                <!-- <b-form-group label="Status" for="status-filter"> -->
                    <label for="status-filter" class="mr-1">Status:</label>
                    <b-form-select v-model="status" id="status-filter">
                        <option value="all">All</option>
                        <option value="pending">Open</option>
                        <option value="overdue">Overdue</option>
                        <option value="complete">Complete</option>
                    </b-form-select>
                <!-- </b-form-group> -->
            </b-col>

            <b-col lg="4" class="d-flex">
                <div class="ml-auto">
                    <b-btn variant="info" @click="create()" :disabled="busy"><i class="fa fa-plus"></i> Create Task</b-btn>
                </div>
            </b-col>
        </b-row>
        <b-row>
            <b-col>
                <b-table bordered striped hover show-empty
                    :items="items"
                    :fields="getFields"
                    :current-page="currentPage"
                    :per-page="perPage"
                    :sort-by.sync="sortBy"
                    :sort-desc.sync="sortDesc"
                    :busy="busy"
                >
                    <template slot="assigned_user" scope="row">
                        <span v-if="row.item.assigned_user">{{ row.item.assigned_user.name }} ({{ row.item.assigned_type }})</span>
                        <span v-else>-</span>
                    </template>
                    <template slot="created_at" scope="row">
                        <span v-if="row.item.created_at">{{ formatDateFromUTC(row.item.created_at) }}</span>
                        <span v-else>-</span>
                    </template>
                    <template slot="due_date" scope="row">
                        <span v-if="row.item.due_date">{{ formatDateFromUTC(row.item.due_date) }}</span>
                        <span v-else>-</span>
                    </template>
                    <template slot="completed_at" scope="row">
                        <span v-if="row.item.completed_at">{{ formatDateFromUTC(row.item.completed_at) }}</span>
                        <span v-else>-</span>
                    </template>
                    <template slot="actions" scope="row">
                        <b-btn size="sm" variant="info" @click.stop="edit(row.item)" :disabled="busy"><i class="fa fa-edit"></i></b-btn>
                        <b-btn size="sm" variant="secondary" @click.stop="view(row.item)" :disabled="busy"><i class="fa fa-eye"></i></b-btn>
                        <b-btn size="sm" variant="danger" @click.stop="destroy(row.item)" :disabled="busy"><i class="fa fa-times"></i></b-btn>
                    </template>
                </b-table>
            </b-col>
        </b-row>
        <b-row>
            <b-col lg="6" >
                <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" :disabled="busy" />
            </b-col>
            <b-col lg="6" class="text-right">
                Showing {{ perPage < totalRows ? perPage : totalRows }} of {{ totalRows }} results
            </b-col>
        </b-row>

        <b-modal id="formModal" :title="modalTitle" v-model="formModal" size="lg">
            <business-task-form :task="task" :office-users="officeUsers" :caregivers="caregivers" ref="form" />

            <div slot="modal-footer">
               <b-btn variant="default" @click="formModal = false" :disabled="busy">Close</b-btn>
               <b-btn variant="info" @click="save()" :disabled="busy">{{ task.id ? 'Save' : 'Create' }}</b-btn>
            </div>
        </b-modal>

        <b-modal id="detailsModal" title="Task Details" v-model="detailsModal" size="lg">
            <business-task-details :task="task" />

            <div slot="modal-footer">
               <b-btn variant="default" @click="detailsModal = false">Close</b-btn>
            </div>
        </b-modal>
    </b-card>
</template>

<script>
    import FormatsDates from "../../../mixins/FormatsDates";

    export default {
        mixins: [ FormatsDates ],

        props: {
            officeUsers: {
                type: Array,
                default: [],
            },
            caregivers: {
                type: Array,
                default: [],
            },
        },

        data: () => ({
            filter: '',
            status: 'pending',

            task: {},
            formModal: false,
            detailsModal: false,
            busy: false,

            items: [],
            perPage: 25,
            currentPage: 1,
            sortBy: 'due_date',
            sortDesc: false,
            fields: [
                {
                    key: 'name',
                    sortable: true,
                },
                {
                    label: 'Created',
                    key: 'created_at',
                    sortable: true,
                },
                {
                    label: 'Due',
                    key: 'due_date',
                    sortable: true,
                },
                {
                    label: 'Assigned To',
                    key: 'assigned_user',
                    sortable: true,
                },
                {
                    label: 'Completed',
                    key: 'completed_at',
                    sortable: true,
                },
                {
                    key: 'actions',
                    class: 'hidden-print'
                },
            ],
        }),

        computed: {
            getFields() {
                if (this.status == 'all' || this.status == 'complete') {
                    return this.fields;
                }

                return this.fields.filter(f => f.key != 'completed_at');
            },
            modalTitle() {
                return this.task.id ? 'Edit Task' : 'Create Task';
            },
            totalRows() {
                return this.items.length;
            }
        },

        methods: {
            fetch() {
                let url = '/business/tasks?';

                if (this.status == 'pending') {
                    url += 'pending=1&';
                } else if (this.status == 'overdue') {
                    url += 'overdue=1&';
                } else if (this.status == 'complete') {
                    url += 'complete=1&';
                }

                if (this.filter == 'created') {
                    url += 'created=1&';
                } else if (this.filter == 'assigned') {
                    url += 'assigned=1&';
                }

                this.busy = true;
                axios.get(url)
                    .then( ({ data }) => {
                        this.items = data;
                        this.busy = false;
                    })
                    .catch(e => {
                        this.busy = false;
                    })
            },

            create() {
                this.task = {};
                this.formModal = true;
            },

            edit(task) {
                this.task = {};
                this.task = task;
                this.formModal = true;
            },
            
            view(task) {
                this.task = {};
                this.task = task;
                this.detailsModal = true;
            },

            save() {
                this.busy = true;
                this.$refs.form.submit()
                    .then(task => {
                        if (this.task.id) {
                            this.items = this.items.filter(obj => obj.id != this.task.id);
                        }
                        this.items.push(task);
                        this.task = {};
                        this.formModal = false;
                        this.busy = false;
                    })
                    .catch(e => {
                        console.log(e);
                        this.busy = false;
                    })
            },

            destroy(task) {
                if (! confirm('Are you sure you want to delete this task?')) {
                    return;
                }

                let f = new Form({});
                
                f.submit('delete', '/business/tasks/' + task.id)
                    .then( ({ data }) => {
                        this.items = this.items.filter(obj => obj.id != task.id);
                        this.task = {};
                        this.formModal = false;
                    })
            },
        },

        mounted() {
            this.fetch();
        },

        watch: {
            status() {
                this.fetch();
            },

            filter() {
                this.fetch();
            },
        }
    }
</script>
