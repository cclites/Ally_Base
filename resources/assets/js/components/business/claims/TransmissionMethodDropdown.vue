<template>
    <b-select v-model="transmission_method" :change="updateValue()">
        <option value="">-- Select Transmission Method --</option>
        <option value="-" disabled>Direct Transmission:</option>
        <option :value="CLAIM_SERVICE.HHA">{{ resolveOption(CLAIM_SERVICE.HHA, claimServiceOptions) }}</option>
        <option :value="CLAIM_SERVICE.TELLUS">{{ resolveOption(CLAIM_SERVICE.TELLUS, claimServiceOptions) }}</option>
<!--        <option :value="CLAIM_SERVICE.CLEARINGHOUSE">{{ resolveOption(CLAIM_SERVICE.CLEARINGHOUSE, claimServiceOptions) }}</option>-->
        <option value="-" disabled>-</option>
        <option value="-" disabled>Offline:</option>
        <option :value="CLAIM_SERVICE.EMAIL">{{ resolveOption(CLAIM_SERVICE.EMAIL, claimServiceOptions) }}</option>
        <option :value="CLAIM_SERVICE.FAX">{{ resolveOption(CLAIM_SERVICE.FAX, claimServiceOptions) }}</option>
    </b-select>
</template>

<script>
    import FormatsStrings from "../../../mixins/FormatsStrings";
    import Constants from "../../../mixins/Constants";

    export default {
        mixins: [ Constants, FormatsStrings ],

        props: {
            value: {
                type: String,
                default: null,
            },
        },

        data() {
            return {
                transmission_method: null,
            }
        },

        methods: {
            updateValue() {
                this.$emit('input', this.transmission_method);
            },

            serviceLabel(serviceValue) {
                switch (serviceValue) {
                    case this.CLAIM_SERVICE.HHA: return 'HHAeXchange';
                    case this.CLAIM_SERVICE.TELLUS: return 'Tellus';
                    case this.CLAIM_SERVICE.CLEARINGHOUSE: return 'CareExchange LTC Clearinghouse';
                    case this.CLAIM_SERVICE.EMAIL: return 'E-Mail';
                    case this.CLAIM_SERVICE.FAX: return 'Fax';
                    default:
                        return '-';
                }
            },
        },

        created() {
            this.transmission_method = this.value;
        }
    }

</script>
