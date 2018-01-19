<template>
    <b-card>
        <b-row v-for="business in businesses">
            <b-col lg="6">
                <b-card>
                    <b-row>
                        <b-col lg="6">
                            <span class="h4 ml-2">{{ business.name }}</span>
                        </b-col>
                        <b-col lg="6">
                            Main point of contact details here
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
        }
    }
</script>