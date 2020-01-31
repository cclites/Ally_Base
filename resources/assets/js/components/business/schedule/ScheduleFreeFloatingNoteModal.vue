<template>

    <b-modal title="Bulk Delete Schedules" v-model="showModal" size="lg" class="modal-fit-more">

        <b-container fluid>

            <b-row>

                <b-col>

                    <b-card title="Create Free Floating Schedule Note">

                        <b-form-group label="End Date" label-for="date" style="margin-bottom: 0;">

                            <date-picker v-model="form.date" />
                            <input-help :form=" form " field="date" text="" />
                        </b-form-group>
                        <b-form-group label="Notes" label-for="notes">

                            <b-textarea id="notes"
                                        :rows="3"
                                        v-model=" form.notes "
                            />
                            <input-help :form="form" field="notes" text="Enter the notes for this schedule day" />
                        </b-form-group>
                    </b-card>
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

            'value' : Boolean,
        },

        data() {

            return {

                daysOfWeek : ['MO', 'TU', 'WE', 'TH', 'FR', 'SA', 'SU'],
                form       : new Form({

                    date  : moment().format( 'MM/DD/YYYY' ),
                    notes : ''
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

        }
    }
</script>

<style>

</style>
