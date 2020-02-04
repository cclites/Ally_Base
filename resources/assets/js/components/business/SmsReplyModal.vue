<template>
    <form @submit.prevent=" submitForm() " @keydown=" form.clearError( $event.target.name ) ">
        <b-modal title="Reply to Text Message"
            v-model="showModal"
            size="lg"
            :no-close-on-backdrop="true"
        >

            <b-row>

                <b-col>

                    <label>Recipient: {{ recipient }}</label>
                </b-col>
                <b-col>

                    <b-form-group class="ml-auto pull-right">
                        <div class="form-check">
                            <label class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" name="can_reply" v-model=" form.can_reply " value="1">
                                <span class="custom-control-indicator"></span>
                                <span class="custom-control-description">Accept Replies</span>
                            </label>
                            <input-help :form="form" field="accepted_terms" text=""></input-help>
                        </div>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row>

                <b-col>

                    <b-form-group label="Message" label-class="required">

                        <b-textarea :rows=" 6 " v-model=" form.message " required :disabled=" form.busy " :state=" form.message.length <= maxLength "></b-textarea>
                        <input-help :form=" form " field="Message" :text=" `Maximum ${maxLength} characters. Currently ${form.message.length}` "></input-help>
                    </b-form-group>
                </b-col>
            </b-row>

            <div slot="modal-footer">

                <b-button variant="success"
                        type="submit"
                        :disabled=" form.busy || form.message.length > maxLength "
                >
                    Send Reply
                </b-button>
                <b-btn variant="default" @click=" showModal = false ">Cancel</b-btn>
            </div>
        </b-modal>
    </form>
</template>

<script>

    import Constants from '../../mixins/Constants';

    export default {

        mixins: [ Constants ],

        components: {},

        props: {

            value : Boolean,
            data : {

                type    : Object,
                default : {}
            },
            continuedThread : Function
        },

        data() {

            return {

                recipient : '',
                'form'      : new Form({

                    original_reply : this.data ? this.data.id : null,
                    can_reply      : 1,
                    all            : 0,
                    message        : '',
                    recipients     : [],
                    business_id    : "",
                    businesses     : '',
                    debug          : false,
                    continued      : 1
                }),
                showModal : this.value,
                maxLength : 290
            }
        },

        computed: {


        },

        methods: {

            makeForm( defaults = {} ) {

                // console.log(defaults);
                this.recipient = defaults.user ? defaults.user.name : '';

                this.form.original_reply = defaults.id || null,
                this.form.can_reply      = 1;
                this.form.all            = 0;
                this.form.message        = '';
                this.form.recipients     = defaults.user ? [ defaults.user.id ] : [];
                this.form.business_id    = defaults.business_id || "";
                this.form.businesses     = '';
                this.form.debug          = false;
                this.form.continued      = 1;
            },

            async submitForm(){

                if ( this.form.message.length > this.maxLength ) {

                    this.form.addError( 'Message', `A maximum character count of ${this.maxLength} is applied to text messages` );
                    return;
                }

                let confirmMessage = 'Are you sure you wish to send this text message to ' + this.recipient + '?';

                if ( !confirm( confirmMessage ) ) return;

                try {

                    this.form.post( `/business/communication/reply-to-reply` )
                        .then( res => {

                            this.$emit( 'continuedThread', { new_thread_id : res.data.data.new_thread_id, reply_id: this.data.id });
                            this.makeForm();
                            this.showModal = false;
                        });
                } catch ( e ) {

                    console.error( e );

                    if ( e.response.status == 418 ) {
                        // Message was sent but there were errors

                        this.makeForm();
                    }

                }
            }
        },

        watch: {

            value( val ) {

                if (! val) {
                    // clear the form on close so the data updates if the
                    // edit modal is opened again for the same object.

                    this.makeForm({});
                } else {

                    this.makeForm( this.data );
                }
                this.showModal = val;
            },
            showModal( val ){

                this.$emit( 'input', val );
            }
        }
    }
</script>

<style scoped>

</style>