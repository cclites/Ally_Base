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
        <step3 v-if="client.setup_status == 'created_account'" 
            :client="client"
            :token="token"
            @updated="updateClient"
        ></step3>
    </div>
</template>

<script>
    import step1 from './clients/Step1';
    import step2 from './clients/Step2';
    import step3 from './clients/Step3';
    
    export default {
        props: {
            'token': {},
            'clientData': {},
        },

        components: { step1, step2, step3 },

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
    }
</script>
