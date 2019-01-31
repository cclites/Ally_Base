<template>
    <div>
        <div class="d-flex my-3 justify-content-between">
            <div class="h4">Sales People</div>
            <div>
                <b-btn variant="info" @click="addSalesperson">
                    <i class="fa fa-plus mr-1"></i>Add Salesperson
                </b-btn>
            </div>
        </div>
        <b-table :items="salesPeople" :fields="fields" show-empty>
            <template slot="actions" scope="data">
                <b-btn title="Edit" @click="editSalesperson(data.item)">
                    <i class="fa fa-edit"></i>
                </b-btn>
                <b-btn variant="danger" title="Delete" @click="confirmDelete(data.item.id)">
                    <i class="fa fa-times"></i>
                </b-btn>
            </template>
        </b-table>

        <b-modal v-model="salesPersonModal.show" :title="salesPersonModal.title">
            <b-form-group label="First Name">
                <b-form-input v-model="salesPersonForm.firstname"></b-form-input>
            </b-form-group>
            <b-form-group label="Last Name">
                <b-form-input v-model="salesPersonForm.lastname"></b-form-input>
            </b-form-group>
            <b-form-group label="Email">
                <b-form-input v-model="salesPersonForm.email"></b-form-input>
            </b-form-group>
            <b-form-group label="Active">
                <b-form-radio-group v-model="salesPersonForm.active">
                    <b-form-radio value="1">Yes</b-form-radio>
                    <b-form-radio value="0">No</b-form-radio>
                </b-form-radio-group>
            </b-form-group>
            <div slot="modal-footer" class="d-flex justify-content-end">
                 <b-btn @click="salesPersonModal.show = false" class="mr-2">Cancel</b-btn>
                 <b-btn variant="info" @click="submit">{{ this.salesPerson.id ? 'Save' : 'Add' }}</b-btn>
            </div>
        </b-modal>
    </div>
</template>

<script>
    export default {
        props: {
            businessId: {
                type: Number,
                required: true
            }
        },

        data () {
            return {
                salesPerson: {},
                salesPersonModal: {
                    show: false,
                    title: '',
                },
                salesPeople: [],
                fields: [
                    {
                        key: 'firstname',
                        label: 'First name',
                        sortable: true
                    },
                    {
                        key: 'lastname',
                        label: 'Last name',
                        sortable: true
                    },
                    {
                        key: 'email',
                        sortable: true
                    },
                    {
                        key: 'active',
                        sortable: true,
                        formatter: (val) => {
                            return parseInt(val) ? 'Yes' : 'No'
                        }
                    },
                    'actions'
                ],
                salesPersonForm: new Form()
            }
        },

        created () {
            this.fetchData()
        },

        methods: {
            async fetchData() {
                let response = await axios.get (`/business/sales-people/${this.businessId}`);
                this.salesPeople = response.data;
            },

            editSalesperson(item) {
                this.salesPerson = item
                this.salesPersonModal.title = 'Edit Salesperson'
                this.salesPersonForm = new Form(item)
                this.salesPersonModal.show = true
            },

            addSalesperson() {
                this.salesPerson = {};
                this.salesPersonForm = new Form({
                    business_id: this.businessId,
                    firstname: '',
                    lastname: '',
                    email: '',
                    active: 1
                });
                this.salesPersonModal.title = 'Add Salesperson'
                this.salesPersonModal.show = true
            },

            async submit() {
                if (! this.salesPerson.id) {
                    let response = await this.salesPersonForm.post('/business/sales-people')
                    this.salesPeople.push(response.data.data)
                    this.salesPersonModal.show = false
                } else {
                    let response = await this.salesPersonForm.put(`/business/sales-people/${this.salesPerson.id}`)
                    let idx = _.findIndex(this.salesPeople, ['id', this.salesPerson.id])
                    Vue.set(this.salesPeople, idx, response.data.data)
                    this.salesPersonModal.show = false
                }
            },

            confirmDelete(id) {
                if (confirm('Are you sure?')) {
                    this.deleteSalesperson(id)
                }
            },

            async deleteSalesperson(id) {
                let form = new Form({});
                form.submit('DELETE', `/business/sales-people/${id}`)
                    .then(response => {
                        let idx = _.findIndex(this.salesPeople, ['id', id])
                        this.salesPeople.splice(idx, 1)        
                    })
                    .catch(e => {
                    })
            }
        }
    }
</script>
