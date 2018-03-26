<template>
    <b-card header="Select the Active Business" header-bg-variant="info" header-text-variant="white">
        <b-form inline @submit.prevent="submitForm()">
            <select class="form-control" v-model="form.business_id" required>
                <option value="">--Select a Business--</option>
                <option v-for="business in businesses" :value="business.id">{{ business.name }}</option>
            </select>
            <b-btn type="submit" variant="info">Switch Business</b-btn>
        </b-form>
    </b-card>
</template>

<script>
    export default {
        props: {
            'business': Object,
        },

        data() {
            return {
                'form': new Form({
                    'business_id': (this.business) ? this.business.id : '',
                }),
                'businesses': [],
            }
        },

        mounted() {
            this.loadBusinesses();
        },

        methods: {
            loadBusinesses() {
                axios.get('/admin/businesses').then(response => this.businesses = response.data);
            },

            async submitForm() {
                const response = await this.form.post('/admin/businesses/active_business');
                window.location.reload();
            }
        },
    }
</script>
