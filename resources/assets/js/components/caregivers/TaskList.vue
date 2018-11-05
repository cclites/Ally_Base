<template>
    <b-card header="Tasks" header-bg-variant="info" header-text-variant="white">
        <b-row class="mb-3">
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
                        <b-btn size="sm" variant="secondary" @click.stop="view(row.item)" :disabled="busy"><i class="fa fa-eye"></i></b-btn>
                        <b-btn v-if="! row.item.completed_at" size="sm" variant="info" @click.stop="complete(row.item)" :disabled="busy"><i class="fa fa-check"></i></b-btn>
                        <b-btn v-else size="sm" variant="info" @click.stop="complete(row.item, false)" :disabled="busy"><i class="fa fa-undo"></i></b-btn>
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

        <b-modal id="detailsModal" title="Task Details" v-model="detailsModal" size="lg">
            <business-task-details :task="task" />

            <div slot="modal-footer">
               <b-btn variant="default" @click="detailsModal = false">Close</b-btn>
            </div>
        </b-modal>
    </b-card>
</template>

<script>
    import FormatsDates from "../../mixins/FormatsDates";

    export default {
        mixins: [ FormatsDates ],

        props: {
        },

        data: () => ({
            filter: '',
            status: 'pending',

            task: {},
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
            totalRows() {
                return this.items.length;
            }
        },

        methods: {
            fetch() {
                let url = '/tasks?';

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

            view(task) {
                this.task = {};
                this.task = task;
                this.detailsModal = true;
            },

            complete(task, onOff = 1) {
                this.busy = true;
                axios.patch(`/tasks/${task.id}`, {complete: onOff})
                    .then(({ data }) => {
                        this.items = this.items.filter(obj => obj.id != data.data.id);
                        this.items.push(data.data);
                        this.task = {};
                        this.busy = false;
                    })
                    .catch(e => {
                        this.busy = false;
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
