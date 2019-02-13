<template>
    <div>
        <step1 v-if="! client.setup_status" 
            :client="client"
            :token="token"
            @updated="updateClient"
        ></step1>
        <step2 v-if="client.setup_status == 'accepted_terms'" 
            :client="client"
            :token="token"
            @updated="updateClient"
        ></step2>
    </div>
</template>

<script>
    import step1 from './clients/Step1';
    import step2 from './clients/Step2';
    
    export default {
        props: {
            'token': {},
            'clientData': {},
        },

        components: { step1, step2 },

        data() {
            return {
                client: this.clientData,
            }
        },
        
        methods: {
            updateClient(data) {
                this.client = data;
            }
        },

        mounted() {
        },
    }
</script>
