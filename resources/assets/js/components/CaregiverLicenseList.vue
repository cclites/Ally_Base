\<template>
    <b-card
        header="Expirations"
        header-text-variant="white"
        header-bg-variant="info"
    >
        <b-row class="align-items-center">

            <b-col>

                <b-btn @click="createLicense()" variant="info" class="mr-2 mb-2" :disabled=" alreadyCreating ">Add Expiration</b-btn>
                <b-btn to="/business/settings#expirations" variant="success" class="mb-2">Manage Expirations</b-btn>
            </b-col>

            <b-form-checkbox
                v-model=" onBlurUpdate "
            >
                Update rows on update
            </b-form-checkbox>
        </b-row>
        <div class="table-responsive">
            <b-table bordered striped hover show-empty
                :busy="loading"
                :items=" chainExpirations "
                :fields="fields"
                :current-page="currentPage"
                :per-page="perPage"
                :filter="filter"
                :sort-by.sync="sortBy"
                :sort-desc.sync="sortDesc"
            >

                <template slot="name" scope="row">

                    <b-form-input
                        v-if=" row.item.isNew "
                        v-model=" row.item.name "
                        placeholder="expiration type"
                        @blur.native=" onBlurUpdate ? saveLicense( row.item ) : null "
                    ></b-form-input>
                    <p class="mb-0" v-else>

                        {{ row.item.name }}
                    </p>
                </template>
                <template slot="description" scope="row">

                    <b-form-input
                        v-model=" row.item.description "
                        placeholder="optional"
                        @blur.native=" onBlurUpdate ? saveLicense( row.item ) : null "
                    ></b-form-input>
                </template>
                <template slot="expires_sort" scope="row">
                    <date-picker
                        v-model=" row.item.expires_at "
                        placeholder="Expiration Date"
                        @input=" onBlurUpdate ? saveLicense( row.item ) : null "
                    ></date-picker>
                </template>
                <template slot="actions" scope="row">

                    <transition name="slide-fade" mode="out-in">

                        <div class="d-flex align-items-center" v-if=" row.item.id " :key=" 'first' ">

                            <b-btn :disabled=" row.item.isLoading " style="max-width: 60px; flex:1" class="mx-1" size="sm" @click=" saveLicense( row.item ) " variant="info">Update</b-btn>
                            <b-btn :disabled=" row.item.isLoading " style="max-width: 35px; flex:1" class="mx-1" size="sm" @click=" deleteLicense( row.item ) " variant="danger"><i class="fa fa-times"></i></b-btn>
                        </div>
                        <div class="d-flex align-items-center" v-else :key=" 'second' ">

                            <b-btn :disabled=" row.item.isLoading " style="max-width: 60px; flex:3" class="mx-1" size="sm" @click=" saveLicense( row.item ) " variant="info">Create</b-btn>
                            <b-btn :disabled=" row.item.isLoading " style="max-width: 35px; flex:1" class="mx-1" size="sm" @click=" removeNew " variant="danger" v-if=" row.item.isNew && alreadyCreating "><i class="fa fa-times"></i></b-btn>
                        </div>
                    </transition>
                </template>
            </b-table>
        </div>
        <b-row>
            <b-col lg="6" >
                <b-pagination :total-rows=" totalRows " :per-page=" perPage " v-model=" currentPage " />
            </b-col>
            <b-col lg="6" class="text-right">
                Showing {{ perPage < totalRows ? perPage : totalRows }} of {{ totalRows }} results
            </b-col>
        </b-row>
    </b-card>
</template>

<script>
    export default {
        props: {
            'caregiverId': {},
            'licenses': {},
        },

        data() {
            return {
                onBlurUpdate : true,
                loading: false,
                perPage: 50,
                currentPage: 1,
                sortBy: null,
                sortDesc: false,
                filter: null,
                fields: [
                    {
                        key: 'name',
                        label: 'Name',
                        sortable: true,
                    },
                    {
                        key: 'description',
                        label: "Notes"
                    },
                    {
                        key: 'expires_sort',
                        label: 'Expiration Date',
                        sortable: true,
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
                chainExpirations : [],
                selectedLicense: null,
            }
        },

        async mounted() {

            // this.totalRows = this.items.length;
            await this.fetchChainExpirations();
        },

        computed: {

            totalRows(){

                return this.chainExpirations.length || 0;
            },

            alreadyCreating(){

                return !!this.chainExpirations.find( exp => exp.isNew );
            }
        },

        methods: {

            async fetchChainExpirations() {

                this.loading = true;
                await axios.get( `/business/expiration-types` )
                    .then( ( { data } ) => {

                        this.licenses.forEach( license => {

                            let existingLicense = data.find( exp => exp.id == license.chain_expiration_type_id );

                            if( existingLicense ){

                                existingLicense.id                       = license.id;
                                existingLicense.chain_expiration_type_id = license.chain_expiration_type_id;
                                existingLicense.name                     = license.name;
                                existingLicense.description              = license.description;
                                existingLicense.expires_at               = license.expires_at;
                                existingLicense.updated_at               = license.updated_at;
                            }
                            else data.push( license );
                        });

                        this.chainExpirations = data.map( exp => {

                            exp.chain_expiration_type_id = exp.expires_at ? exp.chain_expiration_type_id : exp.id;
                            exp.id                       = exp.expires_at ? exp.id : null;
                            exp.name                     = exp.expires_at ? exp.name : exp.type;
                            exp.description              = exp.expires_at ? exp.description : '';
                            exp.expires_at               = exp.expires_at ? moment( exp.expires_at ).format( 'MM/DD/YYYY' ) : '';
                            exp.expires_sort             = exp.expires_at ? moment( exp.expires_at ).format( 'YYYYMMDD' ) : '';
                            exp.updated_at               = exp.expires_at ? moment.utc( exp.updated_at ).local().format( 'MM/DD/YYYY h:mm A' ) : '---';

                            return exp;
                        }).sort( ( a, b ) => a.id - b.id );
                    })
                    .catch( e => {} )
                    .finally( () => {

                        this.loading = false;
                    });
            },

            createLicense() {

                if( !this.alreadyCreating ){

                    const newElement = {

                        isNew       : true,
                        isLoading   : false,
                        name        : '',
                        description : '',
                        expires_at  : '',
                        updated_at  : '---',
                    };
                    this.chainExpirations.unshift( newElement );
                }
            },
            saveLicense( item ){

                item.isLoading = true;
                item.expires_at = item.expires_at;
                let form = new Form( item );

                const verb = item.id ? 'patch' : 'post';
                const url  = '/business/caregivers/' + this.caregiverId + '/licenses' + ( item.id ? '/' + item.id : '' );

                form.submit( verb, url )
                    .then( response => {

                        item.updated_at = moment.utc( response.data.data.updated_at ).local().format( 'MM/DD/YYYY h:mm A' );
                        item.id         = response.data.data.id;
                        item.isNew      = false;
                        item.isLoading = false;
                    })
                    .catch( () => {} )
                    .finally( () => {

                        item.isLoading = false;
                    });
            },
            deleteLicense( license ) {

                let form = new Form();
                if ( confirm( 'Are you sure you wish to delete this certification?' ) ) {

                    license.isLoading = false;

                    form.submit( 'delete', '/business/caregivers/' + this.caregiverId + '/licenses/' + license.id )
                        .then( response => {

                            if( license.chain_expiration_type_id ){
                                // if it belonged to a chain-type, simply reset the form fields and keep it on screen

                                license.id          = null;
                                license.expires_at  = '';
                                license.updated_at  = '---';
                                license.description = '';
                            } else {
                                // else that means this was a one-off expiration, so remove it from the table altogether

                                let i = this.chainExpirations.findIndex( exp => exp.id == license.id );
                                this.chainExpirations.splice( i, 1 );
                            }
                        })
                        .catch( () => {} )
                        .finally( () => {

                            license.isLoading = false;
                        });
                }
            },
            removeNew(){

                let i = this.chainExpirations.findIndex( exp => exp.isNew )
                this.chainExpirations.splice( i, 1 );
            }
        }
    }
</script>

<style>

    td {

        vertical-align: middle !important;
    }
</style>