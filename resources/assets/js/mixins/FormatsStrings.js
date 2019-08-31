export default {
    methods: {
        uppercaseWords(str) {
            return _.startCase(_.camelCase(str));
        },
        stringFormat(str) {
            return this.uppercaseWords(str);
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
        },
        toSnakeCase(str) {
            return _.snakeCase(str);
        },
        stringLimit(str, limit=100)
        {
            if (typeof(str) !== "string") return "";
            return (str.length > 70) ? str.substr(0, 70) + '..' : str;
        },
        fromSnakeCase(str){
           return str.replace(/_/g, ' ');
        },
        snakeToTitleCase(status) {
            return this.uppercaseWords(status.replace('_', ' '));
        },
    }
}
