<template>
    <form @submit.prevent=" submitForm() " @keydown=" form.clearError( $event.target.name ) ">
        <b-modal title="Reply to Text Message"
            v-model="showModal"
            size="lg"
        >

            <b-row>

                <b-col>

                    <label>Recipient: {{ recipient }}</label>
                </b-col>
            </b-row>
            <b-row>

                <b-col>

                    <b-form-group label="Message" label-class="required">

                        <b-textarea :rows=" 6 " v-model=" form.message " required :disabled=" form.busy " :state=" form.message.length <= 140 "></b-textarea>
                        <input-help :form=" form " field="Message" :text=" `Maximum 140 characters. Currently ${form.message.length}` "></input-help>
                    </b-form-group>
                </b-col>
            </b-row>

            <div slot="modal-footer">

                <b-button variant="success"
                        type="submit"
                        :disabled=" form.busy "
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
            }
        },

        data() {

            return {

                recipient : '',
                'form'      : new Form({

                    can_reply   : 1,
                    all         : 0,
                    message     : '',
                    recipients  : [],
                    business_id : "",
                    businesses  : '',
                    debug       : false,
                }),
                showModal : this.value,
            }
        },

        computed: {


        },

        methods: {

            makeForm( defaults = {} ) {

                // console.log(defaults);
                this.recipient = defaults.user ? defaults.user.name : '';

                this.form.can_reply   = 1;
                this.form.all         = 0;
                this.form.message     = '';
                this.form.recipients  = defaults.user ? [ defaults.user.id ] : [];
                this.form.business_id = defaults.business_id || "";
                this.form.businesses  = '';
                this.form.debug       = false;
            },

            async submitForm(){

                if ( this.form.message.length > 140 ) {

                    this.form.addError( 'Message', 'A maximum character count of 140 is applied to text messages' );
                    return;
                }

                let confirmMessage = 'Are you sure you wish to send this text message to ' + this.recipient + '?';

                if ( !confirm( confirmMessage ) ) return;

                try {

                    await this.form.post( `/business/communication/text-caregivers` );
                    this.makeForm();
                    this.showModal = false;
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