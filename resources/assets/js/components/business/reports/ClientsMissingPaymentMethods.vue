<template>
    <b-card>
        <div class="table-responsive">
            <b-table :items="items"
                     show-empty
                     :fields="fields">
            </b-table>
        </div>
    </b-card>
</template>

<script>
    export default {
        props: ['clients'],
        
        data() {
            return {
                fields: [
                    'name',
                    'email',
                ]
            }
        },
        
        computed: {
            items() {
                return _.map(this.clients, (client) => {
                    if (client.shifts.length > 0) {
                        client._rowVariant = 'danger';
                    }
                    return client;
                });
            }
        }
    }
</script>
