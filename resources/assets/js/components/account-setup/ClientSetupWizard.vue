<template>
    <div>
        <step1 v-if="! client.setup_status" 
            :client="client"
            :token="token"
            @updated="updateClient"
        ></step1>
        <step2 v-else-if="client.setup_status == 'accepted_terms'" 
            :client="client"
            :token="token"
            @updated="updateClient"
        ></step2>
        <step3 v-else-if="client.setup_status == 'created_account'" 
            :client="client"
            :token="token"
            @updated="updateClient"
        ></step3>
        <b-card v-else 
            header="Your account is set up!"
            header-bg-variant="info"
            header-text-variant="white"
        >
            <p>You have successfully onboarded.  Please use your username and password to login to our system to manage your details and view invoice and payment history.</p>

            <p>If you forgot your username, please contact (800)-930-0587</p>

            <p>
                <b-btn href="/login" class="success">Login Now</b-btn>
            </p>

            <p>For FAQs and other training material, please go to our <a href="/knowledge-base">Knowledge Base</a>.</p>
        </b-card>
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

        mounted() {
            axios.get(`/account-setup/clients/${this.token}/check`)
                .then( ({ data }) => {
                    this.updateClient(data);
                })
                .catch(e => {
                });
        },
    }
</script>
