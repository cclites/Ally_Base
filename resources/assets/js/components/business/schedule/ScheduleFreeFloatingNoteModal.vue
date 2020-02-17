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
        <div slot="modal-footer">

            <b-btn variant="default" @click=" showModal = false ">Close</b-btn>
            <b-btn variant="info" @click=" save() " :disabled=" form.busy ">

                <i class="fa fa-spinner fa-spin" v-show=" form.busy "></i>
                Create Note
            </b-btn>
        </div>
    </b-modal>
</template>

<script>

    export default {

        mixins: [],

        props: {

            'value'       : Boolean,
            'business_id' : {

                Type    : Number,
                Default : 0
            }
        },

        data() {

            return {

                daysOfWeek : ['MO', 'TU', 'WE', 'TH', 'FR', 'SA', 'SU'],
                form       : new Form({

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
            }
        },

        mounted() {

        },

        methods: {

            save() {

                this.form.post( '/business/schedule-free-floating-notes' )
                    .then( res => {

                        console.log( 'THE RESPONSE: ', res );
                        this.$emit( 'refresh-events' );
                        this.showModal = false;
                    })
                    .catch( err => {

                        console.log( 'THE ERROR: ', err );
                    });
            },
        },

        watch: {

            value(){

                this.form.start_date  = moment().format( 'MM/DD/YYYY' );
                this.form.body        = '';
                this.form.business_id = this.business_id;
            }
        }
    }
</script>

<style>

</style>
