<template>
    <div>
        <step1 v-if="! caregiver.setup_status" 
            :caregiver="caregiver"
            :token="token"
            @updated="updateCaregiver"
        ></step1>
        <step2 v-else-if="caregiver.setup_status == 'confirmed_profile'" 
            :caregiver="caregiver"
            :token="token"
            @updated="updateCaregiver"
        ></step2>
        <step3 v-else-if="caregiver.setup_status == 'created_account'" 
            :caregiver="caregiver"
            :token="token"
            @updated="updateCaregiver"
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
    import step1 from './caregivers/Step1';
    import step2 from './caregivers/Step2';
    import step3 from './caregivers/Step3';
    
    export default {
        props: {
            'token': {},
            'caregiverData': {},
        },

        components: { step1, step2, step3 },

        data() {
            return {
                caregiver: this.caregiverData,
            }
        },
        
        methods: {
            updateCaregiver(data) {
                this.caregiver = data;
            }
        },
    }
</script>
