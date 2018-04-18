<template>
    <b-card
        header="Care Plans"
        header-text-variant="white"
        header-bg-variant="info"
        >
        <b-btn variant="info" class="mb-2" @click="showModal()">Add Care Plan</b-btn>

        <div class="table-responsive">
            <b-table bordered striped hover show-empty
                     :items="items"
                     :fields="fields"
                     :current-page="currentPage"
                     :per-page="perPage"
                     :sort-by.sync="sortBy"
                     :sort-desc.sync="sortDesc"
                     ref="table"
            >
                <template slot="actions" scope="row">
                    <b-btn size="sm" @click="showModal(row.item)">Edit</b-btn>
                    <b-btn size="sm" @click="destroyPlan(row.item)" variant="danger">X</b-btn>
                </template>
                <template slot="updated_at" scope="data">
                    {{ formatDateTimeFromUTC(data.updated_at) }}
                </template>
            </b-table>
        </div>

        <b-modal id="clientPlanModal" :title="modalTitle" v-model="clientPlanModal" ref="clientPlanModal" size="lg">
            <b-container fluid>
                <form @keydown="form.clearError($event.target.name)">
                    <b-row>
                        <b-col lg="12">
                            <b-form-group label="Care Plan Name" label-for="name">
                                <b-form-input
                                    id="name"
                                    name="name"
                                    type="text"
                                    v-model="form.name"
                                    >
                                </b-form-input>
                                <input-help :form="form" field="name" text="Enter the name of the care plan."></input-help>
                            </b-form-group>
                        </b-col>
                    </b-row>
                    <b-row class="mb-3">
                        <b-col lg="12">
                            <h5>Activities</h5>
                            <input-help :form="form" field="activities" text="Check off the activities of daily living that are associated with this care plan."></input-help>
                            <b-row>
                                <b-col cols="12" md="6">
                                    <div class="form-check">
                                        <label class="custom-control custom-checkbox" v-for="activity in leftHalf" :key="activity.id" style="clear: left; float: left;">
                                            <input type="checkbox" class="custom-control-input" v-model="form.activities" :value="activity.id">
                                            <span class="custom-control-indicator"></span>
                                            <span class="custom-control-description">{{ activity.code }} - {{ activity.name }}</span>
                                        </label>
                                    </div>
                                </b-col>
                                <b-col cols="12" md="6">
                                    <div class="form-check">
                                        <label class="custom-control custom-checkbox" v-for="activity in rightHalf" :key="activity.id" style="clear: left; float: left;">
                                            <input type="checkbox" class="custom-control-input" v-model="form.activities" :value="activity.id" >
                                            <span class="custom-control-indicator"></span>
                                            <span class="custom-control-description">{{ activity.code }} - {{ activity.name }}</span>
                                        </label>
                                    </div>
                                </b-col>
                            </b-row>
                        </b-col>
                    </b-row>
                    <b-row>
                        <b-col lg="12">
                            <b-form-group label="Notes" label-for="notes">
                                <b-form-textarea
                                        id="notes"
                                        name="notes"
                                        :rows="3"
                                        v-model="form.notes"
                                >
                                </b-form-textarea>
                                <input-help :form="form" field="notes" text="Enter any notes to attach to schedules."></input-help>
                            </b-form-group>
                        </b-col>
                    </b-row>
                </form>
            </b-container>
            <div slot="modal-footer">
               <b-btn variant="default" @click="clientPlanModal = false">Close</b-btn>
               <b-btn variant="info" @click="submitForm" v-if="selectedPlan">Save Plan</b-btn>
               <b-btn variant="info" @click="submitForm" v-else>Create Plan</b-btn>
            </div>
        </b-modal>

        <b-modal id="confirmDeleteModal" title="Delete Care Plan" v-model="confirmDeleteModal">
            <b-container fluid>
                <h4>Are you sure you want to do delete the care plan "{{ selectedPlan ? selectedPlan.name : '' }}"?</h4>
            </b-container>
            <div slot="modal-footer">
                <b-btn variant="default" @click="confirmDeleteModal = false">Cancel</b-btn>
                <b-btn variant="danger" @click="destroyPlan(selectedPlan, true)" :disabled="submitting">
                    <i class="fa fa-spinner fa-spin" v-show="submitting"></i>
                    Yes, Delete
                </b-btn>
            </div>
        </b-modal>
    </b-card>
</template>

<script>
    import FormatsDates from "../../../mixins/FormatsDates";

    export default {
        props: {
            'client': {},
            'activities': {},
        },

        mixins: [FormatsDates],

        data() {
            return {
                clientPlanModal: false,
                confirmDeleteModal: false,
                submitting: false,

                totalRows: 0,
                perPage: 30,
                currentPage: 1,
                sortBy: null,
                sortDesc: false,
                fields: [
                    {
                        key: 'name',
                        label: 'Name',
                        sortable: true,
                    },
                    {
                        key: 'activities',
                        label: 'Number of ADLs',
                        sortable: true,
                        formatter: (val) => { return val.length }
                    },
                    {
                        key: 'notes',
                        sortable: false,
                        formatter: (val) => { 
                            if (! val || val.length == 0) {
                                return '-';
                            } else if (val.length <= 20) {
                                return val;
                            } else {
                                return val.substring(0, 20) + "...";
                            }
                        }                        
                    },
                    {
                        key: 'updated_at',
                        label: 'Last Updated',
                        sortable: true,
                    },
                    {
                        key: 'actions',
                        class: 'hidden-print'
                    }
                ],
                items: [],
                planModal: false,
                selectedPlan: {},
                form: new Form({
                    name: '',
                    activities: [],
                    notes: '',
                }),
            }
        },

        computed: {
            url() {
                return `/business/clients/${this.client.id}/care-plans`;
            },

            leftHalf() {
                return this.getHalfOfActivities(true);
            },

            rightHalf() {
                return this.getHalfOfActivities(false);
            },

            modalTitle() {
                return (this.selectedPlan) ? 'Edit Care Plan' : 'Add a New Care Plan';
            }
        },

        mounted() {
            axios.get(this.url).then( ({ data }) => {
                this.items = data;
            });
        },

        methods: {
            showModal(plan = null) {
                this.form.reset();

                if (plan) {
                    this.selectedPlan = plan;
                    this.form.name = plan.name;
                    this.form.notes = plan.notes;
                    this.form.activities = this.getPlanActivityList();
                } else {
                    this.selectedPlan = null;
                    this.form = new Form({
                        name: '',
                        activities: [],
                        notes: '',
                    });
                }

                this.clientPlanModal = true;
            },

            destroyPlan(plan, confirmed = false) {

                if (! confirmed) {
                    this.selectedPlan = plan;
                    this.confirmDeleteModal = true;
                    return;
                }

                axios.delete(this.url + `/${plan.id}`)
                    .then( ({ data }) => {
                        let index = this.items.findIndex(item => item.id == plan.id);
                        if (index != -1) {
                            this.items.splice(index, 1);
                        }
                        this.confirmDeleteModal = false;
                    });
            },

            submitForm() {
                let url = this.url;
                let method = 'post';
                if (this.selectedPlan) {
                    url = url + '/' + this.selectedPlan.id;
                    method = 'patch';
                }

                this.form.submit(method, url)
                    .then( ({ data }) => {
                        this.updatePlans(data.data);
                        this.clientPlanModal = false;
                    });
            },

            updatePlans(plan) {
                let index = this.items.findIndex(item => item.id == plan.id);
                if (index != -1) {
                    this.items.splice(index, 1, plan);
                } else {
                    this.items.push(plan);
                }
            },

            getHalfOfActivities(leftHalf = true)
            {
                let half_length = Math.ceil(this.activities.length / 2);
                let clone = this.activities.slice(0);
                let left = clone.splice(0,half_length);
                return (leftHalf) ? left : clone;
            },

            getPlanActivityList() {
                let list = [];

                if (!this.selectedPlan.activities) {
                    return list;
                }

                for (let activity of this.selectedPlan.activities) {
                    list.push(activity.id);
                }

                return list;
            },
        }
    }
</script>

<style>
</style>