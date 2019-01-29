<template>
    <div>
        <b-alert show><strong>Note:</strong> Changes here will affect all office locations.</b-alert>
        <loading-card v-show="loading"></loading-card>

        <b-row v-if="! loading">
            <b-col xs="12" md="6" v-for="(userType, i) in ['caregiver', 'client']" :key="i">
                <div class="d-flex mb-3">
                    <h3 class="f-1">{{ uppercaseWords(userType) }} Statuses</h3>
                    <b-btn variant="info" class="ml-auto" @click="add(userType)">Add New Status</b-btn>
                </div>
                <b-table bordered striped hover show-empty
                    :items="statuses[userType]"
                    :fields="columns"
                >
                    <template slot="active" scope="data">
                        {{ data.value == 1 ? 'Active' : 'Inactive' }}
                    </template>
                    <template slot="actions" scope="data">
                        <b-btn variant="secondary" size="sm" @click="edit(data.item)">
                            <i class="fa fa-edit"></i>
                        </b-btn>
                        <b-btn variant="danger" size="sm" @click="destroy(data.item)">
                            <i class="fa fa-trash"></i>
                        </b-btn>
                    </template>
                </b-table>
            </b-col>
        </b-row>
        <b-modal v-model="statusAliasModal" :title="modalTitle" @ok="save()" @cancel="hideModal()" ok-variant="info">
            <b-form-group label="Status Name">
                <b-form-input v-model="form.name"></b-form-input>
            </b-form-group>
            <b-form-group label="Status Type">
                <b-form-select v-model="form.active" :options="[
                    {text: 'Active', value: '1'},
                    {text: 'Inactive', value: '0'},
                ]"></b-form-select>
            </b-form-group>
        </b-modal>
    </div>
</template>

<script>
    import FormatsStrings from '../../../mixins/FormatsStrings';
    export default {
        mixins: [FormatsStrings],

        props: {
        },

        data() {
            return {
                statusAliasModal: false,
                loading: false,
                statuses: {caregiver: [], client: []},
                columns: [
                    { key: 'name' },
                    { key: 'active', label: 'Active / Inactive' },
                    { key: 'actions', label: '' },
                ],
                form: new Form({
                    id: '',
                    type: '',
                    name: '',
                    active: '1',
                }),
            }
        },

        computed: {
            modalTitle() {
                let verb = 'Add';
                if (this.form.id) {
                    verb = 'Update';
                }
                return verb + ' ' + this.uppercaseWords(this.form.type) + ' Status';
            }
        },
        
        methods: {
            fetch() {
                this.loading = true;
                axios.get(`/business/status-aliases`)
                    .then( ({ data }) => {
                        if (data && data.caregiver) {
                            this.statuses = data;
                        } else {
                            this.statuses = {caregiver: [], client: []};
                        }
                    })
                    .catch(e => {
                    })
                    .finally(() => {
                        this.loading = false;
                    })
            },

            save() {
                let url = '/business/status-aliases';
                if (this.form.id) {
                    url += `/${this.form.id}`;
                    this.form.patch(url)
                        .then( ({ data }) => {
                            let index = this.statuses[this.form.type].findIndex(x => x.id == data.data.id);
                            if (index >= 0) {
                                this.statuses[this.form.type].splice(index, 1, data.data);
                            }
                            this.statusAliasModal = false;
                        })
                        .catch(e => {
                        })
                } else {
                    this.form.post(url)
                        .then( ({ data }) => {
                            this.statuses[this.form.type].push(data.data);
                            this.statusAliasModal = false;
                        })
                        .catch(e => {
                        })
                }
            },

            edit(item) {
                this.form.fill(item);
                this.statusAliasModal = true;
            },

            add(type) {
                this.form.reset(true);
                this.form.type = type;
                this.statusAliasModal = true;
            },

            hideModal() {
                this.statusAliasModal = false;
                this.form.reset(true);
            },

            destroy(item) {
                axios.delete(`/business/status-aliases/${item.id}`)
                    .then(response => {
                        let index = this.statuses[this.form.type].findIndex(x => x.id == item.id);
                        this.statuses[this.form.type].splice(index, 1);
                    })
                    .catch(e => {
                    })
            },
        },
        
        mounted() {
            this.fetch();
        },
    }
</script>
