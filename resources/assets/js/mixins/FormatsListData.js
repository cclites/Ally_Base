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
        }
    }
}
