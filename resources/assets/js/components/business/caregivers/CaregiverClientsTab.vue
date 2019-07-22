<template>
    <b-card
        header="Clients"
        header-text-variant="white"
        header-bg-variant="info"
        >
        <div class="table-responsive">
            <table class="table table-bordered" id="client-cg-table">
                <thead>
                <tr>
                    <th>Client</th>
                </tr>
                </thead>
                <tbody>
                <template v-for="(item,index) in items">
                    <tr>
                        <td>
                            <a :href="`/business/clients/${item.id}`" target="_self">
                                {{ item.firstname }} {{ item.lastname }}
                            </a>
                        </td>
                    </tr>
                </template>
                </tbody>
            </table>
        </div>
    </b-card>
</template>

<script>
    export default {
        props: {
            'caregiver': Object,
        },
        data() {
            return {
                items: [],
            }
        },
        mounted() {
            this.fetchAssignedClients();
        },
        methods: {
            fetchAssignedClients() {
                axios.get('/business/caregivers/' + this.caregiver.id + '/clients')
                    .then(response => {
                        this.items = response.data || [];
                    });
            },
        },
    }
</script>
