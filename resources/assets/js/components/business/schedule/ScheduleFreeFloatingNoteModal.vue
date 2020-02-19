<template>

    <b-modal title="Schedule Free Floating Note Creator" v-model="showModal" size="lg" class="modal-fit-more">

        <b-container fluid>

            <b-row>

                <b-col>

                    <b-form-group label="Date" label-for="start_date" style="margin-bottom: 0;">

                        <date-picker v-model="form.start_date" />
                        <input-help :form=" form " field="start_date" text="" />
                    </b-form-group>
                    <b-form-group label="Notes" label-for="body">

                        <b-textarea id="body"
                                    :rows="3"
                                    v-model=" form.body "
                        />
                        <input-help :form="form" field="body" text="Enter notes for this schedule day" />
                    </b-form-group>
                </b-col>
            </b-row>
        </b-container>
        <div slot="modal-footer" class="w-100">

            <b-btn v-if=" isEditing " variant="danger" @click=" deleteNote() ">Delete</b-btn>

            <b-btn variant="default" @click=" showModal = false " class="pull-right">Close</b-btn>
            <b-btn variant="info" @click=" save() " :disabled=" form.busy " class="pull-right">

                <i class="fa fa-spinner fa-spin" v-show=" form.busy "></i>
                {{ isEditing ? 'Save Edit' : 'Create Note' }}
            </b-btn>
        </div>
    </b-modal>
</template>

<script>

    export default {

        props: {

            'value'       : Boolean,
            'business_id' : {

                Type    : Number,
                Default : 0
            },
            'selectedScheduleNote' : {

                Type    : Object,
                Default : {}
            },
        },

        data() {

            return {

                daysOfWeek : ['MO', 'TU', 'WE', 'TH', 'FR', 'SA', 'SU'],
                form       : new Form({

                    id          : null,
                    start_date  : moment().format( 'MM/DD/YYYY' ),
                    body        : '',
                    business_id : null
                }),
            }
        },

        computed: {

            showModal: {

                get() {

                    return this.value;
                },
                set( value ) {

                    this.$emit( 'input', value );
                }
            },
            isEditing(){

                return this.selectedScheduleNote && !!this.selectedScheduleNote.id;
            }
        },

        mounted() {

        },

        methods: {

            deleteNote(){

                if( !confirm( 'Are you sure you want to delete this schedule note?' ) ) return;

                let form = new Form();
                form.submit( 'delete', `/business/schedule-free-floating-notes/${this.form.id}` )
                    .then( res => {

                        // console.log( 'THE RESPONSE: ', res );
                        this.$emit( 'refresh-events' );
                        this.showModal = false;
                    })
                    .catch( err => {

                        // console.log( 'THE ERROR: ', err );
                    });
            },
            save() {

                const action = this.isEditing ? 'patch' : 'post';
                this.form.submit( action, '/business/schedule-free-floating-notes' + ( this.isEditing ? `/${this.form.id}` : '' ) )
                    .then( res => {

                        // console.log( 'THE RESPONSE: ', res );
                        this.$emit( 'refresh-events' );
                        this.showModal = false;
                    })
                    .catch( err => {

                        // console.log( 'THE ERROR: ', err );
                    });
            },
            resetForm( note = null ){

                this.form.id          = ( note && note.id ) ? note.id : null;
                this.form.start_date  = ( note && note.start_date ) ? moment( note.start_date ).format( 'MM/DD/YYYY' ) : moment().format( 'MM/DD/YYYY' );
                this.form.body        = ( note && note.body ) ? note.body : '';
                this.form.business_id = this.business_id;
            }
        },

        watch: {

            value( newVal, oldVal ){

                const note = _.isEmpty( this.selectedScheduleNote ) ? null : _.cloneDeep( this.selectedScheduleNote );
                this.resetForm( note );
            }
        }
    }
</script>

<style>

</style>
