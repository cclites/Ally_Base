<template>

    <b-card header="Admin Note"
            header-bg-variant="info"
            header-text-variant="white">

        <div class="d-flex flex-column">


            <b-form-group label="Admin Note" label-for="notes">

                <b-form-textarea
                    id="notes"
                    name="notes"
                    :rows="3"
                    v-model="form.body"
                >
                </b-form-textarea>
                <p>Notes for internal admin-use only</p>
            </b-form-group>

            <b-form-group label="&nbsp;">
                <b-btn variant="info" class="pull-right" @click="saveNote()" :disabled="form.busy">Save Note</b-btn>
            </b-form-group>

            <div class="table-responsive">

                <b-table bordered striped hover show-empty
                    :items="notes"
                    :fields="fields"
                    :current-page="currentPage"
                    :per-page="perPage"
                    :sort-by.sync="sortBy"
                    :sort-desc.sync="sortDesc"
                    ref="notesTable"
                >
                    <template slot="creator_user_id" scope="row">

                        {{ row.item.creator.name }}
                    </template>
                    <template slot="body" scope="row">

                        {{ noteBody( row.item.id ) }}
                    </template>
                    <template slot="actions" scope="row">

                        <b-btn size="sm" variant="info" @click.stop=" editNote( row.item ) "><i class="fa fa-edit"></i></b-btn>
                        <b-btn size="sm" variant="danger" @click.stop=" deleteNote( row.item ) "><i class="fa fa-trash"></i></b-btn>
                    </template>
                </b-table>
            </div>
        </div>
    </b-card>
</template>

<script>

    import FormatsDates from '../../mixins/FormatsDates';

    export default {

        mixins: [ FormatsDates ],

        props: {

            user: ''
        },

        data() {

            return {

                totalRows   : 0,
                perPage     : 15,
                currentPage : 1,
                sortBy      : 'created_at',
                sortDesc    : false,
                notes       : [],
                debounce    : true,
                fields      : [
                    {
                        key: 'created_at',
                        label: 'Note Date',
                        formatter: (val) => this.formatDateFromUTC(val),
                        sortable: true,
                    },
                    {
                        key: 'creator_user_id',
                        label: 'Created By',
                        sortable: true,
                    },
                    {
                        key: 'body',
                        label: 'Message',
                        sortable: true,
                    },
                    {
                        key: 'actions',
                        class: 'hidden-print'
                    }
                ],
                form  : new Form({

                    id              : null,
                    creator_user_id : null,
                    subject_user_id : null,
                    body            : ''
                })
            }
        },

        mounted(){

            this.setForm();
            this.getNotes();
        },

        methods : {

            noteBody( id ){

                return this.notes.find( n => n.id == id ).body;
            },
            editNote( note ){

                this.setForm( note );
            },
            deleteNote( note ){

                console.log( 'the note deleting: ', note );
                if( confirm( 'delete this note?' ) ){

                    this.form.delete( `/admin/users/adminNotes/${note.id}` )
                        .then( res => {

                            console.log( 'responses: ', res );
                            const index = this.notes.findIndex( n => n.id == note.id );
                            this.notes.splice( index, 1 );
                        })
                        .catch( err => {

                            console.log( 'ERRORS!! ', err );
                        })
                }
            },
            setForm( data = null ){

                this.form.id              = data ? data.id              : null;
                this.form.subject_user_id = data ? data.subject_user_id : this.user.id;
                this.form.creator_user_id = data ? data.creator_user_id : this.authUser.id;
                this.form.body            = data ? data.body            : '';
            },
            getNotes(){

                let form = new Form({

                    subject_user_id : this.user.id
                });

                form.alertOnResponse = false;

                if( !form.busy ){

                    form.get( `/admin/users/adminNotes` )
                        .then( res => {

                            console.log( 'the response: ', res );
                            this.notes = res.data;
                        })
                        .catch( err => {

                            console.log( 'errors tho: ', err );
                        })
                }
            },
            saveNote(){

                const action = this.form.id ? 'patch' : 'post';

                this.form.submit( action, '/admin/users/adminNotes' + ( this.form.id ? `/${this.form.id}` : '' ) )
                    .then( res => {

                        console.log( 'responses: ', res );
                        console.log( 'has form id', this.form.id );
                        if( this.form.id ){

                            const index = this.notes.findIndex( n => n.id == this.form.id );
                            console.log( 'inside has form id..', index );
                            this.notes[ index ] = res.data.data;
                        } else {

                            console.log( 'strangely got here instead..' );
                            this.notes.push( res.data.data );
                        }
                    })
                    .catch( err => {

                        console.log( 'ERRORS!! ', err );
                    })
                    .finally( () => this.setForm() );
            }
        }
    }
</script>

<style scoped>

</style>