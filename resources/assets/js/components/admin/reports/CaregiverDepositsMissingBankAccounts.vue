<template>
    <b-card>
        <b-row>
            <b-col lg="6" v-for="business in businesses">
                <b-card>
                    <b-row>
                        <b-col lg="6">
                            <span class="h4 ml-2">{{ business.name }}</span>
                        </b-col>
                        <b-col lg="6" class="text-md-right">
                            <div class="ml-2">{{ contactInfo(business) }}</div>
                        </b-col>
                    </b-row>

                    <b-table :items="business.caregivers"
                             :fields="fields">
                    </b-table>
                </b-card>
            </b-col>
        </b-row>
    </b-card>
</template>

<script>
    export default {
        props: ['businesses'],
        
        data() {
            return {
                items: this.caregivers,
                fields: [
                    'name',
                    'email'
                ]
            }
        },

        methods: {
            contactInfo(business) {
                let info = [];
                if (business.contact_name) {
                    info.push(business.contact_name);
                }
                if (business.contact_email) {
                    info.push(business.contact_email);
                }
                if (business.contact_phone) {
                    info.push(business.contact_phone);
                }

                return _.join(info, ', ');
            }
        }
    }
</script>