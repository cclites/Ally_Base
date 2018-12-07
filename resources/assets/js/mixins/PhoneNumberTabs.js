import PhoneNumber from '../components/PhoneNumber';
import AuthUser from './AuthUser';

export default {
    mixins: [ AuthUser ],
    
    props: ['user'],

    components: {
        PhoneNumber
    },

    data() {
        return {
            numbers: [],
            maximumNumbers: 4,
        }
    },

    methods: {
        formatTitle(type) {
            return _.capitalize(type) + ' Number';
        },

        addPhoneNumberIfMissing(type, position = 'last') {
            let index = this.numbers.findIndex(item => item.type === type);
            if (index === -1) {
                this.addPhoneNumber(type, position);
            }
        },

        addPhoneNumber(type = 'home', position = 'last') {
            let number = { type: type, number: '', extension: '', user_id: this.user ? this.user.id : null };
            if (position === 'first') {
                this.numbers.unshift(number);
                return;
            }
            this.numbers.push(number);
        },
    },

    created() {
        this.refreshPhoneNumbers();  // Needs to be implemented on the component using this mixin
    }
}