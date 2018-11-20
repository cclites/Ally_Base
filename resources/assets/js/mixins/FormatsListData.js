import FormatsStrings from "./FormatsStrings";

export default {
    mixins: [FormatsStrings],

    methods: {
        formatEmail(value) {
            if (!value || value.toString().includes('@noemail.allyms.com')) {
                return '';
            }
            return value;
        },
        formatUppercase(value) {
            return this.uppercaseWords(value);
        },
        formatYesNo(value) {
            return this.boolToYesNo(value);
        },
        showBusinessName(businessId) {
            let business = this.$store.state.business.businesses.find(business => business.id == businessId);
            return business ? business.name : "";
        },
    }
}
