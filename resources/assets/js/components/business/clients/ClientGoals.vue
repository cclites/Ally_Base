<template>
    <b-card
        header="Goals"
        header-text-variant="white"
        header-bg-variant="info"
        >
        <b-btn variant="info" class="mb-2" @click="showModal()">Add Goal</b-btn>

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
                <template slot="track_goal_progress" scope="row">
                    {{ row.item.track_goal_progress ? 'Yes' : 'No'}}
                </template>
                <template slot="actions" scope="row">
                    <b-btn size="sm" @click="showModal(row.item)">Edit</b-btn>
                    <b-btn size="sm" @click="destroyGoal(row.item)" variant="danger">X</b-btn>
                </template>
            </b-table>
        </div>

        <b-modal id="clientGoalModal" :title="modalTitle" v-model="clientGoalModal" ref="clientGoalModal" size="lg">
            <b-container fluid> 
                <form @keydown="form.clearError($event.target.name)">
                    <b-row>
                        <b-col lg="12">
                            <b-form-group label="Goal" label-for="question" label-class="required">
                                <b-form-textarea
                                    id="question"
                                    name="question"
                                    :rows="3"
                                    v-model="form.question"
                                    required
                                />
                                <input-help :form="form" field="question" text="Enter the question to be asked upon Clock-out." />
                            </b-form-group>
                        </b-col>
                    </b-row>
                    <b-row>
                        <b-form-checkbox v-model="form.track_goal_progress" :value="true" :unchecked-value="false">
                            Track goal progress on clock-out
                        </b-form-checkbox>
                    </b-row>
                </form>
            </b-container>
            <div slot="modal-footer">
               <b-btn variant="default" @click="clientGoalModal = false">Close</b-btn>
               <b-btn variant="info" @click="submitForm" v-if="selectedGoal.id">Save Goal</b-btn>
               <b-btn variant="info" @click="submitForm" v-else>Create Goal</b-btn>
            </div>
        </b-modal>

        <b-modal id="confirmDeleteModal" title="Delete Goal" v-model="confirmDeleteModal">
            <b-container fluid>
                <h4>Are you sure you want to do delete the Goal "{{ selectedGoal.question }}"?</h4>
            </b-container>
            <div slot="modal-footer">
                <b-btn variant="default" @click="confirmDeleteModal = false">Cancel</b-btn>
                <b-btn variant="danger" @click="destroyGoal(selectedGoal, true)" :disabled="submitting">
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
            'goals': {},
        },

        mixins: [FormatsDates],

        data() {
            return {
                clientGoalModal: false,
                confirmDeleteModal: false,
                submitting: false,
                selectedGoal: {},

                totalRows: 0,
                perPage: 30,
                currentPage: 1,
                sortBy: 'question',
                sortDesc: false,
                fields: [
                    {
                        key: 'question',
                        label: 'Question',
                        sortable: true,
                    },
                    {
                        key: 'track_goal_progress',
                        label: 'Tracked on clock-out',
                        sortable: true,
                    },
                    {
                        key: 'actions',
                        class: 'hidden-print'
                    },
                ],
                items: [],
                form: new Form({
                    question: '',
                    track_goal_progress: true,
                }),
            }
        },

        computed: {
            url() {
                return `/business/clients/${this.client.id}/goals`;
            },

            modalTitle() {
                return (this.selectedGoal.id) ? 'Edit Goal' : 'Add a New Goal';
            },
        },

        mounted() {
            axios.get(this.url).then( ({ data }) => {
                this.items = data;
            });
        },

        methods: {
            showModal(goal = null) {
                this.form.reset();

                if (goal) {
                    this.selectedGoal = goal;
                    this.form.question = goal.question;
                    this.form.track_goal_progress = goal.track_goal_progress;
                } else {
                    this.selectedGoal = {};
                    this.form = new Form({
                        question: '',
                        track_goal_progress: true,
                    });
                }

                this.clientGoalModal = true;
            },

            async destroyGoal(goal, confirmed = false) {
                if (! confirmed) {
                    this.selectedGoal = goal;
                    this.confirmDeleteModal = true;
                    return;
                }

                let form = new Form;
                form.submit('delete', this.goalUrl(goal))
                    .then( ({ data }) => {
                        let index = this.items.findIndex(item => item.id == this.selectedGoal.id);
                        if (index != -1) {
                            this.items.splice(index, 1);
                        }
                        this.selectedGoal = {};
                        this.confirmDeleteModal = false;
                    });
            },

            submitForm() {
                let url = this.url;
                let method = 'post';
                if (this.selectedGoal.id) {
                    url = this.goalUrl(this.selectedGoal);
                    method = 'patch';
                }

                this.form.submit(method, url)
                    .then( ({ data }) => {
                        window.location.reload();
                        //this.updateGoals(data.data);
                        //this.clientGoalModal = false;
                    });
            },

            updateGoals(goal) {
                let index = this.items.findIndex(item => item.id == goal.id);
                if (index != -1) {
                    this.items.splice(index, 1, goal);
                } else {
                    this.items.push(goal);
                }
            },

            goalUrl(goal) {
                return this.url + '/' + goal.id;
            }
        }
    }
</script>

<style>
</style>