<template>
    <div>
        <div class="d-flex mb-3">
            <b-btn variant="info" @click="create()" class="ml-auto">Add Question</b-btn>
        </div>
        <b-table bordered striped hover show-empty
            :items="items"
            :fields="fields"
            :current-page="currentPage"
            :per-page="perPage"
            :sort-by.sync="sortBy"
            :sort-desc.sync="sortDesc"
        >
            <template slot="actions" scope="row">
                <b-btn size="sm" @click.stop="edit(row.item)" :disabled="busy">Edit</b-btn>
                <b-btn size="sm" variant="danger" @click.stop="destroy(row.item)" :disabled="busy">Delete</b-btn>
            </template>
        </b-table>

        <b-modal id="formModal" :title="modalTitle" v-model="formModal" size="lg">
            <question-form :question="question" :business="business" ref="form" />

            <div slot="modal-footer">
               <b-btn variant="default" @click="formModal = false" :disabled="busy">Close</b-btn>
               <!-- <b-btn variant="danger" @click="destroy()" v-if="note.id">Delete</b-btn> -->
               <b-btn variant="info" @click="save()" :disabled="busy">{{ question.id ? 'Save' : 'Create' }}</b-btn>
            </div>
        </b-modal>
    </div>
</template>

<script>
    export default {
        props: {
            business: {
                type: Object,
                default: () => { return {} },
            },
        },

        data: () => ({
            question: {},
            formModal: false,
            busy: false,

            items: [],
            perPage: 25,
            currentPage: 1,
            sortBy: 'question',
            sortDesc: false,
            fields: [
                {
                    key: 'client_type',
                    sortable: true,
                    formatter: x => x ? (x[0].toUpperCase() + x.slice(1)).replace('_', ' ') : 'All',
                },
                {
                    key: 'question',
                    sortable: true,
                },
                {
                    key: 'required',
                    sortable: true,
                    formatter: x => x == 1 ? 'Yes' : 'No',
                },
                {
                    key: 'actions',
                    class: 'hidden-print'
                },
            ],
        }),

        computed: {
            modalTitle() {
                return this.question.id ? 'Edit Question' : 'Create Question';
            },
        },

        methods: {
            create() {
                this.question = {};
                this.formModal = true;
            },

            edit(question) {
                this.question = {};
                this.question = question;
                this.formModal = true;
            },
            
            save() {
                this.busy = true;
                this.$refs.form.submit()
                    .then(question => {
                        if (this.question.id) {
                            this.items = this.items.filter(obj => obj.id != this.question.id);
                        }
                        this.items.push(question);
                        this.question = {};
                        this.formModal = false;
                        this.busy = false;
                    })
                    .catch(e => {
                        console.log(e);
                        this.busy = false;
                    })
            },

            destroy(question) {
                if (! confirm('Are you sure you want to delete this question?')) {
                    return;
                }

                let f = new Form({});
                
                f.submit('delete', '/business/questions/' + question.id)
                    .then( ({ data }) => {
                        this.items = this.items.filter(obj => obj.id != question.id);
                        this.question = {};
                        this.formModal = false;
                    })
            },

            fetch() {
                axios.get(`/business/questions?business=${this.business.id}`)
                    .then( ({ data }) => {
                        this.items = data;
                    })
                    .catch(e => {
                        console.log(e);
                    })
            },
        },

        mounted() {
            this.fetch();
        },

        watch: {
            business(newValue, oldValue) {
                if (newValue) {
                    this.fetch();
                }
            }
        },
    }
</script>
