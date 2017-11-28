<template>
    <div class="row">
        <b-col lg="6" v-for="number in numbers">
            <phone-number
                          :title="formatTitle(number.type)"
                          :type="number.type"
                          :phone="number"
                          @created="refreshPhoneNumbers('The phone number has been saved.')"
                          @updated="refreshPhoneNumbers()"
                          @deleted="removePhoneNumber">
            </phone-number>
        </b-col>

        <b-col lg="6">
            <b-card class="text-center pt-4"
                    style="cursor: pointer;"
                    @click="addPhoneNumber"
                    v-if="numbers.length < 4">
                <i class="fa fa-plus fa-5x"></i>
                <p class="text-center h3">Add New</p>
            </b-card>
        </b-col>
    </div>
</template>

<script>
    import PhoneNumber from '../../PhoneNumber';

    export default {
        props: ['phoneNumbers'],

        components: {
            PhoneNumber
        },

        data() {
            return {
                numbers: this.phoneNumbers
            }
        },

        methods: {
            formatTitle(type) {
                return _.capitalize(type) + ' Number';
            },

            addPhoneNumber() {
                this.numbers.push({ type: 'home', number: '', extension: '' });
            },

            removePhoneNumber() {
                this.refreshPhoneNumbers();
            },

            refreshPhoneNumbers(message = null) {
                axios.get('/profile/phone')
                    .then(response => {
                        this.numbers = response.data;
                        if (message) {
                            alerts.addMessage('success', message);
                        }
                    }).catch(error => {
                        console.error(error.response);
                    });
            }
        }
    }
</script>