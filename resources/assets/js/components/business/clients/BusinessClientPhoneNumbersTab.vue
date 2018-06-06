<template>
    <div class="row">
        <b-col lg="12">
            <small class="pull-right with-padding-bottom">* Any phone number on this page is approved for EVV telephony.</small>
        </b-col>
        <b-col lg="6" v-for="number in numbers" :key="number.id">
            <phone-number
                    :title="formatTitle(number.type)"
                    :type="number.type"
                    :phone="number"
                    :user="user"
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
        props: ['user'],

        components: {
            PhoneNumber
        },

        data() {
            return {
                numbers: this.user.phone_numbers
            }
        },

        methods: {
            formatTitle(type) {
                return _.capitalize(type) + ' Number';
            },

            addPhoneNumber() {
                this.numbers.push({ type: 'home', number: '', extension: '', user_id: this.user.id });
            },

            removePhoneNumber() {
                this.refreshPhoneNumbers();
            },

            refreshPhoneNumbers(message = null) {
                axios.get('/business/phone-numbers/' + this.user.id)
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