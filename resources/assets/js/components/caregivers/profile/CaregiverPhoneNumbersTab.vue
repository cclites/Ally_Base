<template>
    <div class="row">
        <b-col lg="6" v-for="number in numbers" :key="number.id">
            <phone-number
                          :title="formatTitle(number.type)"
                          :type="number.type"
                          :phone="number"
                          :allow-sms="true"
                          @created="refreshPhoneNumbers('The phone number has been saved.')"
                          @updated="refreshPhoneNumbers()"
                          @deleted="refreshPhoneNumbers()">
            </phone-number>
        </b-col>

        <b-col lg="6">
            <b-card class="text-center pt-4"
                    style="cursor: pointer;"
                    @click="addPhoneNumber()"
                    v-if="numbers.length < maximumNumbers">
                <i class="fa fa-plus fa-5x"></i>
                <p class="text-center h3">Add New</p>
            </b-card>
        </b-col>
    </div>
</template>

<script>
    import PhoneNumberTabs from "../../../mixins/PhoneNumberTabs";

    export default {

        mixins: [PhoneNumberTabs],

        methods: {
            refreshPhoneNumbers(message = null) {
                axios.get('/profile/phone')
                    .then(response => {
                        this.numbers = response.data;

                        // Add defaults
                        this.addPhoneNumberIfMissing('primary', 'first');

                        if (message) {
                            alerts.addMessage('success', message);
                        }
                    });
            }
        }
    }
</script>