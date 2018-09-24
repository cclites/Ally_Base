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

                    <b-form-group label="Assigned To" label-for="assigned_user_id">
                        <b-form-select v-model="form.assigned_user_id">
                            <option value="">-- Select Assignee --</option>
                            <option v-for="user in officeUsers" :key="user.id" :value="user.id">{{ user.name }}</option>
                        </b-form-select>
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
    export default {
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
        },

        data() {
            return {
                form: new Form({}),
                busy: false,
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
                    assigned_user_id: data.assigned_user_id,
                    notes: data.notes,
                    completed: data.completed_at ? 1 : 0,
                });
            },
        },

        watch: {
            task(newVal, oldVal) {
                this.fillForm(newVal);
            },
        },

        mounted() {
            this.fillForm({});
        },
    }
</script>