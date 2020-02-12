<template>

    <b-card header="Ally Team Notes"
            header-bg-variant="info"
            header-text-variant="white">

        <div class="d-flex flex-column">
            <b-alert variant="warning">Notes are for internal admins only.</b-alert>

            <b-form-group :label="headerText" label-for="notes">

                <b-form-textarea
                    id="notes"
                    name="notes"
                    :rows="3"
                    v-model="form.body"
                >
                </b-form-textarea>
            </b-form-group>

            <div class="d-flex">
                <div class="ml-auto">
                    <b-btn variant="info" class="mr-2" @click="saveNote()" :disabled="form.busy">{{ buttonText }}</b-btn>
                    <b-btn v-if="form.id" variant="secondary" @click="cancel()" :disabled="form.busy">Cancel</b-btn>
                </div>
            </div>

            <br/>
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
                        {{ row.item.body }}
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

        computed: {
            buttonText() {
                return this.form.id ? 'Save Note' : 'Create Note';
            },
            headerText() {
                return this.form.id ? 'Update Ally Team Note' : 'Add Ally Team Note';
            },
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
            cancel() {
                this.setForm({});
            },

            editNote( note ){

                this.setForm( note );
            },
            deleteNote( note ){

                console.log( 'the note deleting: ', note );
                if( confirm( 'delete this note?' ) ){

                    this.form.delete( `/business/users/admin-notes/${note.id}` )
                        .then( res => {
                            const index = this.notes.findIndex( n => n.id == note.id );
                            this.notes.splice( index, 1 );
                        })
                        .catch( err => {
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

                    form.get( `/business/users/admin-notes` )
                        .then( res => {

                            this.notes = res.data;
                        })
                        .catch( err => {
                        })
                }
            },
            saveNote(){

                const action = this.form.id ? 'patch' : 'post';

                this.form.submit( action, '/business/users/admin-notes' + ( this.form.id ? `/${this.form.id}` : '' ) )
                    .then( res => {

                        if( this.form.id ){

                            console.log(res.data.data);
                            const index = this.notes.findIndex( n => n.id == this.form.id );
                            this.notes.splice(index, 1, res.data.data);
                        } else {

                            this.notes.push( res.data.data );
                        }
                    })
                    .catch( err => {
                    })
                    .finally( () => this.setForm() );
            }
        }
    }
</script>

<style scoped>

</style>