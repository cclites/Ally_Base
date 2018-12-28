<template>
    <b-container fluid>
        <b-row>
            <b-col lg="12">
                <form @submit.prevent="submit()" @keydown="form.clearError($event.target.name)">

                    <b-form-group label="Task Name" label-for="name">
                        <b-form-input
                            id="name"
                            name="name"
                            type="text"
                            v-model="form.name"
                            maxlength="255"
                            :disabled="busy"
                        />
                        <input-help :form="form" field="name" text="" />
                    </b-form-group>

                    <b-form-group label="Notes" label-for="notes">
                        <b-textarea id="notes"
                            :rows="5"
                            v-model="form.notes"
                        />
                        <input-help :form="form" field="notes" text="" />
                    </b-form-group>

                    <business-location-form-group v-model="form.business_id"
                                                   :form="form"
                                                   field="business_id"
                                                   help-text="">
                    </business-location-form-group>

                    <b-form-group>
                        <label label-for="assigned_user_id">Assigned to
                            <span v-if="assignedType == 'Caregiver'">
                                <a href="#" @click.prevent="toggleType()">Staff</a> | <span>Caregiver</span>
                            </span>
                            <span v-else>
                                <span>Staff</span> | <a href="#" @click.prevent="toggleType()">Caregiver</a>
                            </span>
                        </label>
                        <div v-if="assignedType == 'Caregiver'">
                            <b-form-select v-model="form.assigned_user_id">
                                <option value="">-- Select Caregiver --</option>
                                <option v-for="user in caregivers" :key="user.id" :value="user.id">{{ user.name }}</option>
                            </b-form-select>
                        </div>
                        <div v-else>
                            <b-form-select v-model="form.assigned_user_id">
                                <option value="">-- Select Staff Member --</option>
                                <option v-for="user in officeUsers" :key="user.id" :value="user.id">{{ user.name }}</option>
                            </b-form-select>
                        </div>
                        <input-help :form="form" field="assigned_user_id" text="" />
                    </b-form-group>

                    <b-form-group label="Date Due" label-for="due_date">
                        <date-picker
                            id="due_date"
                            v-model="form.due_date"
                            placeholder="Date Due"
                        />
                        <input-help :form="form" field="due_date" text="" />
                    </b-form-group>

                    <b-form-group v-if="task.id">
                        <div class="form-check">
                            <label class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" name="all" v-model="form.completed" value="1">
                                <span class="custom-control-indicator"></span>
                                <span class="custom-control-description">Complete</span>
                            </label>
                            <input-help :form="form" field="accepted_terms" text=""></input-help>
                        </div>
                    </b-form-group>
                </form>
            </b-col>
        </b-row>
    </b-container>
</template>

<script>
    import FormatsDates from "../../../mixins/FormatsDates";
    import BusinessLocationFormGroup from "../BusinessLocationFormGroup";

    export default {
        components: {BusinessLocationFormGroup},

        mixins: [FormatsDates],

        props: {
            task: {
                type: Object,
                default() {
                    return {};
                }
            },
            officeUsers: {
                type: Array,
                default: [],
            },
            caregivers: {
                type: Array,
                default: [],
            },
        },

        data() {
            return {
                form: new Form({}),
                busy: false,
                assignedType: 'staff',
            }
        },

        methods: {
            submit() {
                let path = '/business/tasks';
                let method = 'post';

                if (this.task.id) {
                    path = '/business/tasks/' + this.task.id;
                    method = 'patch';
                }

                this.busy = true;
                return new Promise((resolve, reject) => {
                    this.form.submit(method, path)
                        .then( ({ data }) => {
                            this.busy = false;
                            resolve(data.data);
                        })
                        .catch(e => {
                            this.busy = false;
                            reject(e);
                        });
                });
            },

            fillForm(data) {
                this.form = new Form({
                    name: data.name,
                    due_date: data.due_date ? this.formatDateFromUTC(data.due_date) : '',
                    assigned_user_id: data.assigned_user_id || '',
                    notes: data.notes,
                    completed: data.completed_at ? 1 : 0,
                    business_id: "",
                });
                this.assignedType = data.assigned_type;
            },

            toggleType() {
                if (this.assignedType == 'Caregiver') {
                    this.assignedType = 'Staff';
                    this.assigned_user_id = '';
                } else {
                    this.assignedType = 'Caregiver';
                    this.assigned_user_id = '';
                }
            },
        },

        watch: {
            task(newVal, oldVal) {
                console.log(newVal);
                this.fillForm(newVal);
            },
        },

        mounted() {
            this.fillForm({});
        },
    }
</script>