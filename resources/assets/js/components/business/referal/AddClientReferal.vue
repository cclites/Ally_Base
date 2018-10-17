<template>
    <b-card header="Add New Referal Source"
            header-bg-variant="info"
            header-text-variant="white"
    >
        <form>
            <!--<input type="hidden" name="_token" :value="csrf_token">-->
            <b-row>
                <b-col lg="12">
                    <b-form-group label="Organization" label-for="organization">
                        <b-form-input
                                id="organization"
                                name="organization"
                                v-model="organization"
                                type="text"
                                required
                        >
                        </b-form-input>
                        <div v-if="errmsg.organization" class="error-msg">
                            <span >{{ errmsg.organization }}</span>
                        </div>
                    </b-form-group>
                    <b-form-group label="Name" label-for="name">
                        <b-form-input
                                id="name"
                                name="contact_name"
                                v-model="contact_name"
                                type="text"
                                required
                        >
                        </b-form-input>
                        <div v-if="errmsg.contact_name" class="error-msg">
                            <span >{{ errmsg.contact_name }}</span>
                        </div>
                    </b-form-group>
                    <b-form-group label="Phone" label-for="phone">
                        <b-form-input
                                id="phone"
                                name="phone"
                                v-model="phone"
                                type="text"
                                required
                        >
                        </b-form-input>
                        <div v-if="errmsg.phone" class="error-msg">
                           <span >{{ errmsg.phone }}</span>
                        </div>
                    </b-form-group>
                    <div class="text-center">
                        <b-button variant="success"
                                  type="button"
                                  @click="checkForm"
                                  v-if="!loading"

                        >
                            Create
                        </b-button>
                        <b-button variant="default" v-if="showbtn" @click="closeModal">Close</b-button>
                    </div>

                    <div class="loader" v-if="loading"></div>
                </b-col>
            </b-row>
        </form>
    </b-card>
</template>

<script>
    export default {
        props: ['showStatus'],

        data() {
            return {
                organization: null,
                contact_name: null,
                phone: null,
                // csrf_token: '',
                loading: false,
                errmsg: '',
                showbtn: false
            }
        },

        mounted() {
            // this.csrf_token = $('meta[name="csrf-token"]').attr('content');
            this.showbtn = this.showStatus;
        },

        methods: {
            checkForm() {
                if(this.organization && this.contact_name && this.phone) {
                    this.loading = true;
                    axios .post('/business/add/client-referal', {
                        organization:  this.organization,
                        contact_name: this.contact_name,
                        phone: this.phone
                    }) .then(response => {
                        if(response.data.errors) {
                            this.errmsg = response.data.errors;
                        }

                        if(response.data.status) {
                            this.organization = '',
                            this.contact_name  = '',
                            this.phone = '',
                            this.errmsg = ''
                            this.$emit('refsource', response.data.refsourc);
                        }
                    }).finally(() => this.loading = false)
                }
            },

            closeModal() {
                this.$emit('closemodal', false);
            }
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