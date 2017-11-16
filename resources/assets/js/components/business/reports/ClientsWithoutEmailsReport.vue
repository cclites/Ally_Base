<template>
    <b-card title="Clients without Email">
        <b-table :items="items" :fields="fields">
            <template slot="link_to_client" scope="data">
                <a :href="'/business/clients/' + data.item.id" class="btn btn-secondary">
                    <i class="fa fa-edit"></i>
                </a>
            </template>
        </b-table>
    </b-card>
</template>

<script>
    export default {
        props: ['clients'],
        
        data() {
            return{
                items: this.clients,
                fields: [
                    'name',
                    'link_to_client'
                ]
            }
        }
    }
</script>