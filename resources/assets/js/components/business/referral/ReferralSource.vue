<template>
        <b-modal id="EditReferralModal" v-model="showModal">

            <div slot="modal-footer">
                <b-btn variant="default" @click="showModal=false">Close</b-btn>
            </div>
        </b-modal>
</template>

<script>
    export default {
        props: {
            value: Boolean,
            source: '',
            sourceType: {
                type: String,
                default: 'client',
            }
        },

        data() {
            return {
                //form: this.makeForm(this.source),
                //form: [],
                loading: false,
                showModal: this.value,
                organization: '',
                items: this.source,
                totalRows: 0,
                currentPage: 1,
                perPage: 25,
                filter: null,
                search: null,
                sortBy: 'organization',
                sortDesc: false,
                fields: [
                    {
                        key: 'organization',
                        label: 'Organization',
                        sortable: true
                    },
                    {
                        key: 'contact_name',
                        label: 'Contacts',
                        sortable: true,
                    },

                    'actions'
                ]
            }
        },

        computed: {

        },

        methods: {
            makeForm(defaults = {}) {
                return new Form({});
                /*
                return new Form({
                    organization: defaults.organization,
                    contact_name: defaults.contact_name,
                    phone: defaults.phone,
                    type: this.sourceType,
                });*/
            },

            submitForm() {
                /*
                this.loading = true;
                let method = this.source.id ? 'patch' : 'post';
                let url = this.source.id ? `/business/referral-sources/${this.source.id}` : '/business/referral-sources';
                this.form.submit(method, url)
                    .then(response => {
                        this.$emit('saved', response.data.data);
                        this.showModal = false;
                    })
                    .finally(() => this.loading = false)
                    */
            },

            save(){
                //TODO
            },

            destroy(){
                //TODO
            }

        },

        watch: {
            /*
            value(val) {
                this.form = this.makeForm(this.source);
                this.showModal = val;
            },


            showModal(val) {
                this.$emit('input', val);
            }

             */

        }
    }
</script>

<style scoped>
    .loader {
        border: 8px solid #f3f3f3;
        border-radius: 50%;
        border-top: 8px solid #3498db;
        width: 50px;
        height: 50px;
        -webkit-animation: spin-data-v-7012acc5 2s linear infinite;
        animation: spin-data-v-7012acc5 2s linear infinite;
        margin: 0 auto;
    }

    /* Safari */
    @-webkit-keyframes spin {
        0% { -webkit-transform: rotate(0deg); }
        100% { -webkit-transform: rotate(360deg); }
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .error-msg {
        margin-top: 7px;
        color: red;
    }
</style>