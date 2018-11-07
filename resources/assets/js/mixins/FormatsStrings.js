export default {
    methods: {
        uppercaseWords(str) {
            return _.startCase(_.camelCase(str));
        },
        stringFormat(str) {
            return _.startCase(_.camelCase(str));
        },
        boolToYesNo(bool) {
            return bool ? 'Yes' : 'No';
        },
        addressFormat(address, html = false) {
            let br = html ? "<br />" : "\n";
            if (!address || !address.address1) return '';
            let str = address.address1;
            if (address.address2) str = str + br + address.address2;
            str = str + br + address.city + `, ` + address.state + ' ' + address.zip;
            return str;
        }
    }
}
