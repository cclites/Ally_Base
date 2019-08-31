<template>
    <b-modal id="editClaimModal" title="Edit Claim" v-model=" showModal " size="lg" class="modal-fit-more">
        <claim-details v-if=" claim.id "
                       :claim=" claim "
                       :transmitUpdate=" transmitUpdate "
                       :transmitDelete=" transmitDelete "
                       :transmitEditItem=" transmitEditItem "
        />

        <div slot="modal-footer">
            <slot name="buttons">
                <b-btn variant="default" @click=" showModal = false ">Close</b-btn>
            </slot>
        </div>
    </b-modal>
</template>

<script>
    export default {
        props: {
            value: false,
            claim: {
                type    : Object,
                default : () => {},
            },
            transmitUpdate   : Function,
            transmitDelete   : Function,
            transmitEditItem : Function
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
        },
    }
</script>
