<template>
    <b-card
        header="Expirations"
        header-text-variant="white"
        header-bg-variant="info"
    >
        <b-row class="mb-2 align-items-center">

            <b-col lg="6" class="flex-column flex-sm-row">

                <b-btn @click=" createExpirationModal = true " variant="info" style="flex:1" class="my-1 d-flex d-sm-inline-block" :disabled=" alreadyCreating ">Add Custom Expiration ( this caregiver only )</b-btn>
                <b-btn to="/business/settings#expirations" variant="success" style="flex:1" class="my-1 d-flex d-sm-inline-block">Manage Default Expirations</b-btn>
                <b-form-checkbox class="m-0 vertical-center" v-model=" hide_inapplicables ">Hide any expirations inapplicable to the caregiver</b-form-checkbox>
            </b-col>
            <b-col lg="6" class="text-right d-flex justify-content-end align-items-center">
                <b-btn :disabled=" loading || updateList.length == 0 " class="ml-3" @click=" saveLicenses() " variant="success">Save Expirations</b-btn>
            </b-col>
        </b-row>
        <div class="table-responsive">

            <b-table bordered striped hover show-empty
                :busy="loading"
                :items=" filteredExpirations "
                :fields="fields"
                :filter="filter"
                sort-by="expires_sort"
                :sort-desc=" false "
                no-sort-reset
            >

                <template slot="name" scope="row">

                    <b-form-input
                        :disabled=" !!row.item.chain_expiration_type_id "
                        :class=" row.item.applicable ? '' : 'text-muted' "
                        v-model=" row.item.name "
                        @change.native=" addToUpdateList( row.item, row.item.name ) "
                    ></b-form-input>
                </template>
                <template slot="description" scope="row">

                    <b-form-input
                        v-if=" row.item.applicable "
                        v-model=" row.item.description "
                        :state=" nameState( row.item.description ) "
                        @change.native=" addToUpdateList( row.item, row.item.description ) "
                        trim
                    ></b-form-input>
                    <b-form-invalid-feedback id="input-live-feedback">
                        Maximum 80 characters
                    </b-form-invalid-feedback>
                </template>
                <template slot="expires_sort" scope="row">
                    <date-picker
                        v-if=" row.item.applicable "
                        v-model=" row.item.expires_at "
                        @input=" addToUpdateList( row.item, row.item.expires_at ) "
                    ></date-picker>
                </template>
                <template slot="updated_at" scope="row">

                    <p class="mb-0 text-center" :class=" row.item.applicable ? '' : 'text-muted' ">

                        {{ row.item.updated_at }}
                    </p>
                </template>
                <template slot="actions" scope="row">

                    <transition name="slide-fade" mode="out-in">

                        <div class="d-flex align-items-center justify-content-center" v-if=" !row.item.applicable " :key=" 'fourth' ">

                            <!-- <b-btn :disabled=" row.item.isLoading " style="max-width: 60px; flex:3" class="mx-1" size="sm" @click=" saveLicense( row.item ) " variant="info">Create</b-btn> -->
                            <b-btn :disabled=" row.item.isLoading " style="max-width: 30px; flex:1" class="mx-1" size="sm" @click=" toggleApplicable( row.item ) " title="Mark Applicable for this Caregiver" v-b-tooltip.hover><i class="fa fa-eye"></i></b-btn>
                        </div>
                        <div class="d-flex align-items-center justify-content-center" v-else-if=" row.item.id " :key=" 'first' ">

                            <!-- <b-btn :disabled=" row.item.isLoading " style="max-width: 60px; flex:1" class="mx-1" size="sm" @click=" saveLicense( row.item ) " variant="info">Update</b-btn> -->
                            <b-btn :disabled=" row.item.isLoading " style="max-width: 30px; flex:1" class="mx-1" size="sm" @click=" deleteLicense( row.item ) " title="Remove Expiration" v-b-tooltip.hover><i class="fa fa-trash"></i></b-btn>
                            <b-btn :disabled=" row.item.isLoading " style="max-width: 30px; flex:1" class="mx-1" size="sm" v-if=" row.item.chain_expiration_type_id " @click=" toggleApplicable( row.item ) " title="Mark Unapplicable for this Caregiver" v-b-tooltip.hover><i class="fa fa-times"></i></b-btn>
                        </div>
                        <div class="d-flex align-items-center justify-content-center" v-else-if=" row.item.isNew && alreadyCreating " :key=" 'second' ">

                            <!-- <b-btn :disabled=" row.item.isLoading " style="max-width: 60px; flex:3" class="mx-1" size="sm" @click=" saveLicense( row.item ) " variant="info">Create</b-btn> -->
                            <b-btn :disabled=" row.item.isLoading " style="max-width: 30px; flex:1" class="mx-1" size="sm" @click=" removeNew " title="Remove Expiration" v-b-tooltip.hover><i class="fa fa-trash"></i></b-btn>
                        </div>
                        <div class="d-flex align-items-center justify-content-center" v-else-if=" row.item.applicable " :key=" 'third' ">

                            <!-- <b-btn :disabled=" row.item.isLoading " style="max-width: 60px; flex:3" class="mx-1" size="sm" @click=" saveLicense( row.item ) " variant="info">Create</b-btn> -->
                            <b-btn :disabled=" row.item.isLoading " style="max-width: 30px; flex:1" class="mx-1" size="sm" @click=" toggleApplicable( row.item ) " title="Mark Unapplicable for this Caregiver" v-b-tooltip.hover><i class="fa fa-times"></i></b-btn>
                        </div>
                    </transition>
                </template>
            </b-table>
        </div>
        <b-row class="align-items-center">

            <b-col lg="12" class="text-right d-flex justify-content-end align-items-center">
                <b-btn :disabled=" loading || updateList.length == 0 " class="ml-3" @click=" saveLicenses() " variant="success">Save Expirations</b-btn>
            </b-col>
        </b-row>

        <b-modal id="expirationsModal" title="Expiration Details" v-model=" createExpirationModal ">

            <b-container fluid>

                <b-row class="d-flex flex-column">

                    <b-form-group label="Expiration Date" label-for="expires_at">

                        <date-picker
                            v-model=" form.expires_at "
                            name="expires_at"
                        ></date-picker>
                    </b-form-group>
                    <b-form-group label="Expiration Name" label-for="name">

                        <b-form-input
                            id="name"
                            name="name"
                            type="text"
                            v-model="form.name"
                            >
                        </b-form-input>
                    </b-form-group>
                    <b-form-group label="Expiration Description" label-for="description">

                        <b-form-input
                            id="description"
                            name="description"
                            type="text"
                            v-model="form.description"
                            >
                        </b-form-input>
                    </b-form-group>
               </b-row>
            </b-container>
            <div slot="modal-footer">

               <b-btn variant="default" @click=" createExpirationModal = false ">Close</b-btn>
               <b-btn variant="info" @click=" saveLicense( form ) " >Save</b-btn>
            </div>
        </b-modal>
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

                createExpirationModal : false,
                hide_inapplicables : true,
                updateList : [],
                loading: false,
                filter: null,
                form : {},
                fields: [
                    {
                        key: 'name',
                        label: 'Name',
                        class: 'name-column',
                        sortable: true,
                    },
                    {
                        key: 'expires_sort',
                        class: 'expiration-column',
                        label: 'Expiration Date',
                        sortable: true,
                    },
                    {
                        key: 'description',
                        label: "Notes",
                        sortable: true,
                    },
                    {
                        key: 'updated_at',
                        class: 'updated-column',
                        label: 'Last Updated',
                        sortable: true,
                    },
                    {
                        key: 'actions',
                        class: 'actions-column hidden-print',
                        sortable: false,
                    }
                ],
                chainExpirations : [],
                selectedLicense: null,
            }
        },

        async mounted() {

            this.createForm();
            this.fetchChainExpirations();
        },

        computed: {

            alreadyCreating(){

                return !!this.chainExpirations.find( exp => exp.isNew );
            },

            filteredExpirations(){

                return this.chainExpirations.filter( exp => {

                    return this.hide_inapplicables ? exp.applicable : exp;
                });
            }
        },

        methods: {

            toggleApplicable( item ){
                // only 'default expirations' can be appliable or inapplicable.
                // applicability is tracked by a specific relationship between the caregiver and the expiration.

                let j = this.updateList.findIndex( tempId => tempId == item.tempId );
                if( j >= 0 ) this.updateList.splice( j, 1 );

                item.applicable = !item.applicable;

                if( item.applicable ){
                    // if applicable, delete the specific relationship so that the record could show up as a blank expiration ready to set

                    this.deleteLicense( item, true );
                } else {
                    // if inapplicable, create the specific relationship that denotes this.

                    item.expires_at  = '01/01/1337';
                    item.description = 'inapplicable';
                    this.saveLicense( item );
                }
            },
            addToUpdateList( item, val ){

                if( !this.updateList.includes( item.tempId ) ) this.updateList.push( item.tempId );
                if( this.updateList.includes( item.tempId ) && [ null, '' ].includes( val ) ){

                    const index = this.updateList.findIndex( tempId => tempId == item.tempId );
                    this.updateList.splice( index, 1 );
                }
            },
            nameState( value ) {

                if( [ null, '' ].includes( value ) || value.length <= 80 ) return null;
                return false;
            },
            async fetchChainExpirations() {

                this.loading = true;
                await axios.get( `/business/expiration-types` )
                    .then( ( { data } ) => {

                        // console.log( 'response: ', data );

                        this.licenses.forEach( license => {

                            let existingLicense = data.find( exp => exp.id == license.chain_expiration_type_id );

                            if( existingLicense ){

                                // console.log( 'if existing.. ', existingLicense );

                                existingLicense.id                       = license.id;
                                existingLicense.chain_expiration_type_id = license.chain_expiration_type_id;
                                existingLicense.name                     = existingLicense.type;
                                existingLicense.description              = license.description;
                                existingLicense.expires_at               = license.expires_at;
                                existingLicense.updated_at               = license.updated_at;
                            }
                            else data.push( license );
                        });

                        this.chainExpirations = data.map( exp => {

                            exp.tempId                   = Math.floor( Math.random() * 10000 ),
                            exp.chain_expiration_type_id = exp.expires_at ? exp.chain_expiration_type_id : exp.id;
                            exp.id                       = exp.expires_at ? exp.id : null;
                            exp.name                     = exp.expires_at ? exp.name : exp.type;
                            exp.description              = exp.expires_at ? exp.description : '';
                            exp.expires_at               = exp.expires_at ? moment( exp.expires_at ).local().format( 'MM/DD/YYYY' ) : '';
                            exp.expires_sort             = exp.expires_at ? moment( exp.expires_at ).local().format( 'YYYYMMDD' ) : '';
                            exp.updated_at               = exp.expires_at ? moment.utc( exp.updated_at ).local().format( 'MM/DD/YYYY h:mm A' ) : '---';

                            exp.isLoading                = false;
                            exp.applicable               = exp.description == 'inapplicable' ? false : true;

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

                        tempId      : Math.floor( Math.random() * 10000 ),
                        isNew       : true,
                        isLoading   : false,
                        name        : '',
                        description : '',
                        expires_at  : '',
                        updated_at  : '---',
                        applicable  : true
                    };
                    this.chainExpirations.unshift( newElement );
                }
            },
            saveLicense( item ){
                // only in use for the toggling applicable status

                item.isLoading = true;
                item.expires_at = item.expires_at; // is this necessary?
                let form = new Form( item );

                const verb = item.id ? 'patch' : 'post';
                const url  = '/business/caregivers/' + this.caregiverId + '/licenses' + ( item.id ? '/' + item.id : '' );

                form.submit( verb, url )
                    .then( response => {

                        if( item.isNew ) this.chainExpirations.unshift( item );

                        item.updated_at = moment.utc( response.data.data.updated_at ).local().format( 'MM/DD/YYYY h:mm A' );
                        item.id         = response.data.data.id;
                        item.isNew      = false;
                    })
                    .catch( () => {} )
                    .finally( () => {

                        item.isLoading = false;
                        this.createExpirationModal = false;
                    });
            },
            saveLicenses(){

                this.loading = true;

                let expirationsToSave = this.chainExpirations.filter( exp => {

                    if( this.updateList.find( tempId => exp.tempId == tempId ) ) return exp;
                })

                let form = new Form( expirationsToSave );
                form.submit( 'post', '/business/caregivers/' + this.caregiverId + '/licenses/saveMany' )
                    .then( res => {
                        // sync the data, id is not always present so match by name

                        res.data.data.forEach( updated => {

                            let exp        = this.chainExpirations.find( exp => exp.name == updated.name );
                            exp.updated_at = moment.utc( updated.updated_at ).local().format( 'MM/DD/YYYY h:mm A' );
                            exp.id         = updated.id;
                            exp.isNew      = false;
                        });
                        this.updateList = [];
                    })
                    .catch( () => {} )
                    .finally( () => {

                        this.loading = false;
                    });
            },
            deleteLicense( license, goahead = false ) {

                let form = new Form();
                if ( goahead || confirm( 'Are you sure you wish to delete this certification?' ) ) {

                    license.isLoading = true;

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

                                let j = this.updateList.findIndex( tempId => tempId == license.tempId );
                                this.updateList.splice( j, 1 );
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
            },
            createForm(){

                this.form = {

                    expires_at  : moment().format( 'MM/DD/YYYY' ),
                    tempId      : Math.floor( Math.random() * 10000 ),
                    isNew       : true,
                    isLoading   : false,
                    name        : '',
                    description : '',
                    updated_at  : '',
                    applicable  : true
                };
            }
        },
        watch: {

            createExpirationModal( newVal, oldVal ){

                this.createForm();
            }
        }
    }
</script>

<style>

    td {

        vertical-align: middle !important;
    }

    .name-column {

        width: 205px;
    }
    .expiration-column {

        width: 145px;
    }
    .updated-column {

        width: 185px;
    }
    .actions-column {

        width: 55px;
    }
</style>