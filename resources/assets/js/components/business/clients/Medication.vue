<template>
    <b-card
        header="Medications"
        header-text-variant="white"
        header-bg-variant="info"
    >
        <b-btn variant="info" class="mb-2" @click="showModal()">Add Medication</b-btn>
        <ally-table id="client-medication" :columns="fields" :items="items" sort-by="">
            <template slot="actions" scope="row">
                <b-btn size="sm" @click="showModal(row.item)">Edit</b-btn>
                <b-btn size="sm" @click="destroyMedication(row.item)" variant="danger">X</b-btn>
            </template>
        </ally-table>

        <b-modal id="editModal" :title="modalTitle" v-model="editModal" ref="editModal" size="lg">
            <b-container fluid> 
                <form @keydown="form.clearError($event.target.name)">
                    <b-form-group label="Name" label-for="name" label-class="required">
                        <b-form-input v-model="form.type" required />
                        <input-help :form="form" field="type" text="Enter the name of this medicine." />
                    </b-form-group>
                    <b-row>
                        <b-col lg="6">
                            <b-form-group label="Dose" label-for="dose" label-class="required">
                                <b-form-input 
                                    id="dose"
                                    v-model="form.dose" 
                                    name="dose"
                                    required
                                />
                                <input-help :form="form" field="dose" text="" />
                            </b-form-group>
                        </b-col>
                        <b-col lg="6">
                            <b-form-group label="Frequency" label-for="frequency" label-class="required">
                                <b-form-input 
                                    id="frequency"
                                    v-model="form.frequency" 
                                    name="frequency"
                                    required
                                />
                                <input-help :form="form" field="frequency" text="" />
                            </b-form-group>
                        </b-col>
                    </b-row>
                    <b-form-group label="Description" label-for="description" label-class="required">
                        <b-form-textarea
                            id="description"
                            name="description"
                            v-model="form.description"
                            :rows="3"
                            required
                        />
                        <input-help :form="form" field="description" text="Enter the description of this medicine." />
                    </b-form-group>
                    <b-form-group label="Side effects" label-for="side_effects">
                        <b-form-textarea
                            id="side_effects"
                            name="side_effects"
                            v-model="form.side_effects"
                            :rows="3"
                        />
                        <input-help :form="form" field="side_effects" text="Enter information about the side effects of this medicine." />
                    </b-form-group>
                    <b-form-group label="Notes" label-for="notes">
                        <b-form-textarea
                            id="notes"
                            name="notes"
                            v-model="form.notes"
                            :rows="3"
                        />
                        <input-help :form="form" field="notes" text="You may enter some additional notes here." />
                    </b-form-group>
                    <b-form-group label="Tracking" label-for="tracking">
                        <b-form-input 
                            id="tracking"
                            v-model="form.tracking" 
                            name="tracking"
                        />
                        <input-help :form="form" field="tracking" text="" />
                    </b-form-group>
                </form>
            </b-container>
            <div slot="modal-footer">
               <b-btn variant="default" @click="editModal = false">Close</b-btn>
               <b-btn variant="info" @click="submit">{{ selected.id ? 'Save' : 'Create' }} Medication</b-btn>
            </div>
        </b-modal>

        <b-modal id="confirmDeleteModal" title="Delete Medication" v-model="confirmDeleteModal">
            <b-container fluid>
                <h4>Are you sure you want to do delete the medication "{{ selected.name }}"?</h4>
            </b-container>
            <div slot="modal-footer">
                <b-btn variant="default" @click="confirmDeleteModal = false">Cancel</b-btn>
                <b-btn variant="danger" @click="destroyMedication(selected, true)" :disabled="submitting">
                    <i class="fa fa-spinner fa-spin" v-show="submitting" />
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
            client: {
                type: Object,
                required: true,
            },
            medications: {
                type: Array,
                required: true,
            },
        },

        mixins: [FormatsDates],

        data() {
            return {
                editModal: false,
                confirmDeleteModal: false,
                submitting: false,
                selected: {},
                fields: [
                    {
                        key: 'type',
                        label: 'Name',
                        sortable: true,
                        shouldShow: true,
                    },
                    {
                        key: 'dose',
                        sortable: true,
                        shouldShow: true,
                    },
                    {
                        key: 'frequency',
                        sortable: true,
                        shouldShow: true,
                    },
                    {
                        key: 'description',
                        sortable: true,
                        shouldShow: true,
                    },
                    {
                        key: 'tracking',
                        sortable: true,
                        shouldShow: true,
                    },
                    {
                        key: 'side_effects',
                        sortable: false,
                        shouldShow: true,
                    },
                    {
                        key: 'actions',
                        class: 'hidden-print',
                        sortable: false,
                        shouldShow: true,
                    },
                ],
                items: [],
                form: new Form({
                    type: '',
                    description: '',
                    side_effects: '',
                    dose: '',
                    frequency: '',
                    notes: '',
                    tracking: '',
                }),
            }
        },

        computed: {
            modalTitle() {
                const action = this.selected.id ? 'Edit' : 'Add a New';
                return `${action} Medication`;
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
                    this.selected = goal;
                    this.form.question = goal.question;
                    this.form.track_goal_progress = goal.track_goal_progress;
                } else {
                    this.selected = {};
                    this.form = new Form({
                        question: '',
                        track_goal_progress: true,
                    });
                }

                this.editModal = true;
            },

            async destroyMedication(goal, confirmed = false) {
                if (! confirmed) {
                    this.selected = goal;
                    this.confirmDeleteModal = true;
                    return;
                }

                let form = new Form;
                form.submit('delete', this.goalUrl(goal))
                    .then( ({ data }) => {
                        let index = this.items.findIndex(item => item.id == this.selected.id);
                        if (index != -1) {
                            this.items.splice(index, 1);
                        }
                        this.selected = {};
                        this.confirmDeleteModal = false;
                    });
            },

            async submit() {
                try {
                    const url = `/business/clients/${this.client.id}/medications`;
                    const {data: {data}} = this.selected.id 
                        ? await this.form.patch(url)
                        : await this.form.post(url);
                    this.editModal = false;
                    this.updateMedication(data);
                }catch(e) {
                    console.error(e);
                }
            },

            updateMedication(medication) {
                const index = this.items.findIndex(item => item.id == medication.id);
                if (index != -1) {
                    this.items.splice(index, 1, medication);
                } else {
                    this.items[index] = medication;
                }
            },
        }
    }
</script>

<style>
</style>