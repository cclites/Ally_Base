<template>
    <div>
        <div class="row mb-2">
            <div class="col-md-12">
                <b-btn @click="copyAddress">Copy Service to Billing Address</b-btn>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-6 col-sm-12">
                <user-address :hasNotes="true" title="Service Address <small class='text-right float-right'>* EVV GPS Verification checks against this address.</small>" type="evv" :action="'/business/clients/'+clientId+'/address/evv'" :address="serviceAddress"></user-address>
            </div>
            <div class="col-lg-6 col-sm-12">
                <user-address title="Billing Address <small class='mb-3'></small>" type="billing" :action="'/business/clients/'+clientId+'/address/billing'" :address="billingAddress"></user-address>
            </div>
        </div>
    </div>

</template>

<script>
    export default {
        props: ['addresses', 'clientId'],

        data() {
            return{
                serviceAddress: {},
                billingAddress: {}
            }
        },

        created() {
            let service = _.find(this.addresses, address => {
                return address.type === 'evv';
            });

            this.serviceAddress = service ? service : {};

            let billing = _.find(this.addresses, address => {
                return address.type === 'billing';
            });

            this.billingAddress = billing ? billing : {};
        },

        methods: {
            copyAddress() {
                this.$set(this, 'billingAddress', this.serviceAddress);
            }
        }
    }
</script>

<style>

</style>